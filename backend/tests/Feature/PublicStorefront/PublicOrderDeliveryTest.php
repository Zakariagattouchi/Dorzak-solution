<?php

namespace Tests\Feature\PublicStorefront;

use App\Models\DeliveryProvider;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Services\PlanGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicOrderDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        ['store' => $this->store] = $this->createStoreWithOwner(['whatsapp' => '+974 5555 1234']);
        $this->store->update(['latitude' => 25.2854, 'longitude' => 51.531]);
        $this->store->storefrontSetting->update([
            'online_store_enabled' => true, 'slug' => 'delivery-shop',
            'allow_delivery' => true, 'min_order_amount' => 0,
        ]);
        $this->assignPlan($this->store, 'PRO');
    }

    private function product(float $price = 30): Product
    {
        return Product::factory()->for($this->store)->create([
            'price' => $price, 'stock' => 50, 'taxable' => false, 'is_active' => true, 'show_in_online_store' => true,
        ]);
    }

    private function payload(Product $p, array $overrides = []): array
    {
        return array_merge([
            'customer' => [
                'name' => 'Jane', 'phone' => '+974 5555 9999', 'address' => 'Pearl Tower 3',
                'latitude' => 25.325, 'longitude' => 51.531,
            ],
            'fulfillment' => 'delivery',
            'items' => [['product_id' => $p->id, 'quantity' => 1]],
        ], $overrides);
    }

    public function test_placement_recomputes_the_quote_and_snapshots_the_provider(): void
    {
        $provider = DeliveryProvider::create(['name' => 'Courier', 'base_fee' => 5, 'per_km_fee' => 2, 'min_fee' => 0, 'max_radius_km' => 20]);
        $p = $this->product();

        $response = $this->postJson('/api/public/stores/delivery-shop/orders', $this->payload($p))
            ->assertCreated();

        $fee = (float) $response->json('data.delivery_fee');
        $this->assertGreaterThan(5, $fee); // base + per-km × ~4.4km

        $this->assertDatabaseHas('orders', [
            'delivery_provider_id' => $provider->id,
            'delivery_provider_name' => 'Courier',
            'delivery_fee_status' => 'QUOTED',
        ]);
        $this->assertNotNull($response->json('data.total'));
    }

    public function test_client_sent_fee_is_ignored(): void
    {
        DeliveryProvider::create(['name' => 'Courier', 'base_fee' => 5, 'per_km_fee' => 2, 'min_fee' => 0, 'max_radius_km' => 20]);
        $p = $this->product();

        $response = $this->postJson('/api/public/stores/delivery-shop/orders', $this->payload($p, [
            'delivery_fee' => 0.01, 'total' => 1,
        ]))->assertCreated();

        $this->assertGreaterThan(5, (float) $response->json('data.delivery_fee'));
    }

    public function test_out_of_radius_without_fallback_is_rejected(): void
    {
        DeliveryProvider::create(['name' => 'Tiny', 'base_fee' => 5, 'per_km_fee' => 2, 'min_fee' => 0, 'max_radius_km' => 1]);
        $p = $this->product();

        $this->postJson('/api/public/stores/delivery-shop/orders', $this->payload($p))
            ->assertStatus(409)
            ->assertJsonPath('code', 'DELIVERY_UNAVAILABLE');
    }

    public function test_free_threshold_still_waives_the_provider_fee(): void
    {
        DeliveryProvider::create(['name' => 'Courier', 'base_fee' => 5, 'per_km_fee' => 2, 'min_fee' => 0, 'max_radius_km' => 20]);
        $this->store->storefrontSetting->update(['free_delivery_threshold' => 20]);
        $p = $this->product(30); // subtotal 30 >= 20

        $this->postJson('/api/public/stores/delivery-shop/orders', $this->payload($p))
            ->assertCreated()
            ->assertJsonPath('data.delivery_fee', '0.00');
    }

    public function test_delivery_without_a_pin_is_rejected(): void
    {
        DeliveryProvider::create(['name' => 'Courier', 'base_fee' => 5, 'per_km_fee' => 2, 'min_fee' => 0, 'max_radius_km' => 20]);
        $p = $this->product();

        $payload = $this->payload($p);
        unset($payload['customer']['latitude'], $payload['customer']['longitude']);

        $this->postJson('/api/public/stores/delivery-shop/orders', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['customer.latitude']);
    }

    public function test_legacy_flat_fee_still_applies_with_no_providers(): void
    {
        $this->store->storefrontSetting->update(['delivery_fee' => 7]);
        $p = $this->product();

        $this->postJson('/api/public/stores/delivery-shop/orders', $this->payload($p))
            ->assertCreated()
            ->assertJsonPath('data.delivery_fee', '7.00');

        $this->assertDatabaseHas('orders', ['delivery_fee_status' => null, 'delivery_provider_id' => null]);
    }

    public function test_delivery_order_snapshots_the_pickup_point_for_the_courier(): void
    {
        DeliveryProvider::create(['name' => 'Courier', 'base_fee' => 5, 'per_km_fee' => 2, 'min_fee' => 0, 'max_radius_km' => 20]);
        $this->store->update(['address' => '742 Evergreen Terrace', 'city' => 'Doha', 'country' => 'Qatar']);
        $p = $this->product();

        $this->postJson('/api/public/stores/delivery-shop/orders', $this->payload($p))->assertCreated();

        $order = Order::withoutGlobalScopes()->latest('id')->first();
        $this->assertEquals(25.2854, (float) $order->pickup_latitude);
        $this->assertEquals(51.531, (float) $order->pickup_longitude);
        $this->assertSame('742 Evergreen Terrace, Doha, Qatar', $order->pickup_address);
    }

    public function test_pickup_point_survives_the_store_moving_afterwards(): void
    {
        DeliveryProvider::create(['name' => 'Courier', 'base_fee' => 5, 'per_km_fee' => 2, 'min_fee' => 0, 'max_radius_km' => 20]);
        $p = $this->product();

        $this->postJson('/api/public/stores/delivery-shop/orders', $this->payload($p))->assertCreated();

        // The store relocates (or clears its pin) — the in-flight order keeps its
        // original collection point.
        $this->store->update(['latitude' => null, 'longitude' => null]);

        $order = Order::withoutGlobalScopes()->latest('id')->first();
        $this->assertEquals(25.2854, (float) $order->pickup_latitude);
    }

    public function test_pickup_orders_carry_no_pickup_point(): void
    {
        $this->store->storefrontSetting->update(['allow_pickup' => true]);
        $p = $this->product();

        $this->postJson('/api/public/stores/delivery-shop/orders', [
            'customer' => ['name' => 'Jane', 'phone' => '+974 5555 9999'],
            'fulfillment' => 'pickup',
            'items' => [['product_id' => $p->id, 'quantity' => 1]],
        ])->assertCreated();

        $order = Order::withoutGlobalScopes()->latest('id')->first();
        $this->assertNull($order->pickup_latitude);
    }

    public function test_whatsapp_message_carries_both_legs_of_the_trip(): void
    {
        DeliveryProvider::create(['name' => 'Courier', 'base_fee' => 5, 'per_km_fee' => 2, 'min_fee' => 0, 'max_radius_km' => 20]);
        $this->store->update(['address' => '742 Evergreen Terrace', 'city' => 'Doha']);
        $p = $this->product();

        $url = $this->postJson('/api/public/stores/delivery-shop/orders', $this->payload($p))
            ->assertCreated()->json('data.whatsapp_url');
        $text = rawurldecode(substr($url, strpos($url, 'text=') + 5));

        $this->assertStringContainsString('Collect from: 742 Evergreen Terrace, Doha', $text);
        $this->assertStringContainsString('maps.google.com/?q=25.2854', $text);  // pickup
        $this->assertStringContainsString('Deliver to: https://maps.google.com/?q=25.325', $text);
    }

    public function test_a_store_without_a_pickup_point_cannot_take_delivery_even_with_fallback_on(): void
    {
        DeliveryProvider::create(['name' => 'Courier', 'base_fee' => 5, 'per_km_fee' => 2, 'min_fee' => 0, 'max_radius_km' => 20]);
        $this->store->update(['latitude' => null, 'longitude' => null]);
        $this->store->storefrontSetting->update(['whatsapp_delivery_fallback' => true]);
        $p = $this->product();

        $this->postJson('/api/public/stores/delivery-shop/orders', $this->payload($p))
            ->assertStatus(409)
            ->assertJsonPath('code', 'DELIVERY_UNAVAILABLE')
            ->assertJsonPath('details.reason', 'STORE_LOCATION_MISSING');
    }

    public function test_customer_can_choose_a_dearer_courier_and_is_charged_that_price(): void
    {
        $cheap = DeliveryProvider::create(['name' => 'Slow Courier', 'base_fee' => 5, 'per_km_fee' => 1, 'min_fee' => 0, 'max_radius_km' => 20]);
        $fast = DeliveryProvider::create(['name' => 'Express', 'base_fee' => 20, 'per_km_fee' => 3, 'min_fee' => 0, 'max_radius_km' => 20]);
        $p = $this->product();

        $response = $this->postJson('/api/public/stores/delivery-shop/orders', $this->payload($p, [
            'delivery_provider_id' => $fast->id,
        ]))->assertCreated();

        // Priced as Express, not as the cheaper default.
        $this->assertSame('Express', $response->json('data.delivery_provider_name'));
        $order = Order::withoutGlobalScopes()->latest('id')->first();
        $this->assertSame($fast->id, $order->delivery_provider_id);
        $this->assertGreaterThan(20, (float) $order->delivery_fee);
        $this->assertNotSame($cheap->id, $order->delivery_provider_id);
    }

    public function test_cheapest_courier_is_used_when_the_customer_picks_nothing(): void
    {
        DeliveryProvider::create(['name' => 'Express', 'base_fee' => 20, 'per_km_fee' => 3, 'min_fee' => 0, 'max_radius_km' => 20]);
        $cheap = DeliveryProvider::create(['name' => 'Slow Courier', 'base_fee' => 5, 'per_km_fee' => 1, 'min_fee' => 0, 'max_radius_km' => 20]);
        $p = $this->product();

        $this->postJson('/api/public/stores/delivery-shop/orders', $this->payload($p))
            ->assertCreated()
            ->assertJsonPath('data.delivery_provider_name', 'Slow Courier');

        $this->assertSame($cheap->id, Order::withoutGlobalScopes()->latest('id')->first()->delivery_provider_id);
    }

    public function test_choosing_an_out_of_range_courier_is_rejected_not_silently_swapped(): void
    {
        DeliveryProvider::create(['name' => 'Wide', 'base_fee' => 5, 'per_km_fee' => 1, 'min_fee' => 0, 'max_radius_km' => 20]);
        $tiny = DeliveryProvider::create(['name' => 'Tiny radius', 'base_fee' => 1, 'per_km_fee' => 1, 'min_fee' => 0, 'max_radius_km' => 1]);
        $p = $this->product();

        $this->postJson('/api/public/stores/delivery-shop/orders', $this->payload($p, [
            'delivery_provider_id' => $tiny->id,
        ]))
            ->assertStatus(409)
            ->assertJsonPath('code', 'DELIVERY_OPTION_UNAVAILABLE');
    }

    public function test_a_store_without_the_delivery_perk_cannot_pick_a_plan_gated_courier(): void
    {
        DeliveryProvider::create(['name' => 'City Courier', 'base_fee' => 10, 'per_km_fee' => 1, 'min_fee' => 0, 'max_radius_km' => 20]);
        $perk = DeliveryProvider::create(['name' => 'Dorzak Delivery', 'base_fee' => 1, 'per_km_fee' => 1, 'min_fee' => 0, 'max_radius_km' => 20, 'is_plan_gated' => true]);

        // PRO sells online but strip DELIVERY_SERVICES so the perk rate is locked.
        $this->store->subscription->plan->featureLimits()->where('feature', 'DELIVERY_SERVICES')->delete();
        app(PlanGate::class)->forget($this->store);
        $p = $this->product();

        // Even naming the perk courier explicitly must not unlock its cheaper rate.
        $this->postJson('/api/public/stores/delivery-shop/orders', $this->payload($p, [
            'delivery_provider_id' => $perk->id,
        ]))
            ->assertStatus(409)
            ->assertJsonPath('code', 'DELIVERY_OPTION_UNAVAILABLE');
    }

    public function test_fallback_accepts_out_of_range_orders_as_fee_pending(): void
    {
        DeliveryProvider::create(['name' => 'Tiny', 'base_fee' => 5, 'per_km_fee' => 2, 'min_fee' => 0, 'max_radius_km' => 1]);
        $this->store->storefrontSetting->update(['whatsapp_delivery_fallback' => true]);
        $p = $this->product();

        $response = $this->postJson('/api/public/stores/delivery-shop/orders', $this->payload($p))
            ->assertCreated()
            ->assertJsonPath('data.delivery_fee', '0.00')
            ->assertJsonPath('data.delivery_fee_status', 'PENDING');

        // The WhatsApp text tells the merchant to quote and carries the pin.
        $text = rawurldecode(substr($response->json('data.whatsapp_url'), strpos($response->json('data.whatsapp_url'), 'text=') + 5));
        $this->assertStringContainsString('TO BE QUOTED', $text);
        $this->assertStringContainsString('maps.google.com', $text);
    }

    public function test_fallback_orders_cannot_pay_by_fawran_upfront(): void
    {
        Storage::fake('local');
        DeliveryProvider::create(['name' => 'Tiny', 'base_fee' => 5, 'per_km_fee' => 2, 'min_fee' => 0, 'max_radius_km' => 1]);
        $this->store->storefrontSetting->update(['whatsapp_delivery_fallback' => true, 'fawran_enabled' => true]);
        $p = $this->product();

        // A receipt is attached, so this clears the "no proof" gate and lands on
        // the stronger guard: a transfer can't happen before the fee is known.
        $this->post('/api/public/stores/delivery-shop/orders', $this->payload($p, [
            'payment_method' => 'FAWRAN',
            'payment_proof' => UploadedFile::fake()->image('receipt.jpg'),
        ]), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['payment_method']);
    }

    public function test_whatsapp_message_includes_the_maps_pin_and_provider_line(): void
    {
        DeliveryProvider::create(['name' => 'Courier', 'base_fee' => 5, 'per_km_fee' => 2, 'min_fee' => 0, 'max_radius_km' => 20]);
        $p = $this->product();

        $url = $this->postJson('/api/public/stores/delivery-shop/orders', $this->payload($p))
            ->assertCreated()
            ->json('data.whatsapp_url');

        $text = rawurldecode(substr($url, strpos($url, 'text=') + 5));
        $this->assertStringContainsString('https://maps.google.com/?q=25.325', $text);
        $this->assertStringContainsString('Delivery (Courier', $text);
    }
}
