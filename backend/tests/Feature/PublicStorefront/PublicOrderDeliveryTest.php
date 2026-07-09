<?php

namespace Tests\Feature\PublicStorefront;

use App\Models\DeliveryProvider;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
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
        DeliveryProvider::create(['name' => 'Tiny', 'base_fee' => 5, 'per_km_fee' => 2, 'min_fee' => 0, 'max_radius_km' => 1]);
        $this->store->storefrontSetting->update(['whatsapp_delivery_fallback' => true, 'fawran_enabled' => true]);
        $p = $this->product();

        $this->postJson('/api/public/stores/delivery-shop/orders', $this->payload($p, ['payment_method' => 'FAWRAN']))
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
