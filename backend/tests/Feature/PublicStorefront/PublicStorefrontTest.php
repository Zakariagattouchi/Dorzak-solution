<?php

namespace Tests\Feature\PublicStorefront;

use App\Models\Product;
use App\Models\Store;
use App\Notifications\OnlineOrderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PublicStorefrontTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        ['store' => $this->store] = $this->createStoreWithOwner([
            'whatsapp' => '+1 (555) 234-5678', 'currency' => 'USD', 'charge_sales_tax' => true, 'tax_rate' => 10,
        ]);
        $this->store->storefrontSetting->update([
            'online_store_enabled' => true, 'slug' => 'dorzak-merchant',
            'allow_delivery' => true, 'allow_pickup' => true,
            'delivery_fee' => 5, 'free_delivery_threshold' => 50, 'min_order_amount' => 10,
            'show_out_of_stock_online' => true,
        ]);
        // Online ordering is a paid capability.
        $this->assignPlan($this->store, 'PRO');
    }

    private function product(array $attrs = []): Product
    {
        return Product::factory()->for($this->store)->create(array_merge([
            'price' => 100, 'stock' => 50, 'taxable' => true, 'is_active' => true, 'show_in_online_store' => true,
        ], $attrs));
    }

    public function test_store_card_is_public(): void
    {
        $this->store->storefrontSetting->update([
            'product_card_layout' => 'horizontal',
            'show_store_header' => false,
            'show_store_gradient' => false,
        ]);

        $this->getJson('/api/public/stores/dorzak-merchant')
            ->assertOk()
            ->assertJsonPath('data.business_name', $this->store->name)
            ->assertJsonPath('data.currency', 'USD')
            ->assertJsonPath('data.product_card_layout', 'horizontal')
            ->assertJsonPath('data.show_store_header', false)
            ->assertJsonPath('data.show_store_gradient', false);
    }

    public function test_disabled_store_returns_404(): void
    {
        $this->store->storefrontSetting->update(['online_store_enabled' => false]);

        $this->getJson('/api/public/stores/dorzak-merchant')->assertNotFound();
        $this->getJson('/api/public/stores/dorzak-merchant/catalog')->assertNotFound();
    }

    public function test_unknown_slug_returns_404(): void
    {
        $this->getJson('/api/public/stores/nope')->assertNotFound();
    }

    public function test_catalog_only_shows_online_active_products(): void
    {
        $this->product(['name' => 'Visible']);
        $this->product(['name' => 'Hidden', 'show_in_online_store' => false]);
        $this->product(['name' => 'Inactive', 'is_active' => false]);

        $this->getJson('/api/public/stores/dorzak-merchant/catalog')
            ->assertOk()
            ->assertJsonCount(1, 'data.products')
            ->assertJsonPath('data.products.0.name', 'Visible');
    }

    public function test_out_of_stock_hidden_when_setting_off(): void
    {
        $this->store->storefrontSetting->update(['show_out_of_stock_online' => false]);
        $this->product(['name' => 'InStock', 'stock' => 5]);
        $this->product(['name' => 'OOS', 'stock' => 0]);

        $this->getJson('/api/public/stores/dorzak-merchant/catalog')
            ->assertOk()
            ->assertJsonCount(1, 'data.products');
    }

    public function test_public_resources_do_not_leak_private_fields(): void
    {
        $keys = array_keys($this->getJson('/api/public/stores/dorzak-merchant')->json('data'));

        $this->assertNotContains('email', $keys);
        $this->assertNotContains('tax_id', $keys);
        $this->assertNotContains('owner_name', $keys);
    }

    public function test_online_order_is_pending_whatsapp_and_returns_wa_url(): void
    {
        Notification::fake();
        $p = $this->product(['price' => 100]);

        $response = $this->postJson('/api/public/stores/dorzak-merchant/orders', [
            'customer' => ['name' => 'Jane', 'phone' => '+1 555-7777'],
            'fulfillment' => 'pickup',
            'items' => [['product_id' => $p->id, 'quantity' => 1]],
        ])->assertCreated();

        $response->assertJsonPath('data.status', 'CONFIRMING')
            ->assertJsonPath('data.total', '110.00'); // 100 + 10% tax, pickup = no delivery
        $this->assertStringStartsWith('https://wa.me/15552345678?text=', $response->json('data.whatsapp_url'));

        $this->assertDatabaseHas('orders', ['status' => 'CONFIRMING', 'payment_method' => 'WHATSAPP', 'source' => 'ONLINE']);
    }

    public function test_dine_in_order_requires_configured_table_and_uses_cash_at_table(): void
    {
        Notification::fake();
        $this->store->storefrontSetting->update(['allow_dine_in' => true, 'dine_in_table_count' => 8]);
        $p = $this->product(['price' => 40, 'taxable' => false]);

        $response = $this->postJson('/api/public/stores/dorzak-merchant/orders', [
            'customer' => ['name' => 'Table Guest', 'phone' => '+974 5555 1111'],
            'fulfillment' => 'dine_in',
            'table_number' => 5,
            'payment_method' => 'CASH',
            'items' => [['product_id' => $p->id, 'quantity' => 1]],
        ])->assertCreated();

        $response
            ->assertJsonPath('data.fulfillment', 'DINE_IN')
            ->assertJsonPath('data.table_number', 5)
            ->assertJsonPath('data.delivery_fee', '0.00');
        $this->assertStringStartsWith('https://wa.me/', $response->json('data.whatsapp_url'));

        $this->assertDatabaseHas('orders', [
            'fulfillment' => 'DINE_IN',
            'table_number' => 5,
            'payment_method' => 'CASH',
            'payment_status' => 'UNPAID',
        ]);
    }

    public function test_dine_in_rejects_unconfigured_table_number(): void
    {
        Notification::fake();
        $this->store->storefrontSetting->update(['allow_dine_in' => true, 'dine_in_table_count' => 2]);
        $p = $this->product(['taxable' => false]);

        $this->postJson('/api/public/stores/dorzak-merchant/orders', [
            'customer' => ['name' => 'Table Guest', 'phone' => '+974 5555 1111'],
            'fulfillment' => 'dine_in',
            'table_number' => 9,
            'payment_method' => 'CASH',
            'items' => [['product_id' => $p->id, 'quantity' => 1]],
        ])->assertStatus(422)->assertJsonValidationErrors('table_number');
    }

    public function test_pending_online_order_does_not_deduct_stock(): void
    {
        Notification::fake();
        $p = $this->product(['stock' => 30]);

        $this->postJson('/api/public/stores/dorzak-merchant/orders', [
            'customer' => ['name' => 'Jane', 'phone' => '+1 555-7777'],
            'fulfillment' => 'pickup',
            'items' => [['product_id' => $p->id, 'quantity' => 5]],
        ])->assertCreated();

        $this->assertSame(30, $p->fresh()->stock);
    }

    public function test_delivery_fee_applied_and_waived_over_threshold(): void
    {
        Notification::fake();
        $cheap = $this->product(['price' => 20, 'taxable' => false]);
        $pricey = $this->product(['price' => 60, 'taxable' => false]);

        // Delivery orders now always carry the customer's pin.
        $pin = ['latitude' => 25.3, 'longitude' => 51.5];

        // Below threshold (50) -> 5 delivery fee.
        $this->postJson('/api/public/stores/dorzak-merchant/orders', [
            'customer' => ['name' => 'A', 'phone' => '+1 555-1', ...$pin],
            'fulfillment' => 'delivery',
            'items' => [['product_id' => $cheap->id, 'quantity' => 1]],
        ])->assertCreated()
            ->assertJsonPath('data.delivery_fee', '5.00')
            ->assertJsonPath('data.total', '25.00');

        // At/above threshold -> free delivery.
        $this->postJson('/api/public/stores/dorzak-merchant/orders', [
            'customer' => ['name' => 'B', 'phone' => '+1 555-2', ...$pin],
            'fulfillment' => 'delivery',
            'items' => [['product_id' => $pricey->id, 'quantity' => 1]],
        ])->assertCreated()
            ->assertJsonPath('data.delivery_fee', '0.00');
    }

    public function test_min_order_not_met_returns_409(): void
    {
        Notification::fake();
        $this->store->storefrontSetting->update(['min_order_amount' => 30]);
        $p = $this->product(['price' => 10, 'taxable' => false]);

        $this->postJson('/api/public/stores/dorzak-merchant/orders', [
            'customer' => ['name' => 'A', 'phone' => '+1 555-1'],
            'fulfillment' => 'pickup',
            'items' => [['product_id' => $p->id, 'quantity' => 1]],
        ])->assertStatus(409)->assertJsonPath('code', 'MIN_ORDER_NOT_MET');
    }

    public function test_online_order_upserts_customer_by_phone(): void
    {
        Notification::fake();
        $p = $this->product(['taxable' => false]);

        $payload = [
            'customer' => ['name' => 'Jane', 'phone' => '+1 555-7777'],
            'fulfillment' => 'pickup',
            'items' => [['product_id' => $p->id, 'quantity' => 1]],
        ];
        $this->postJson('/api/public/stores/dorzak-merchant/orders', $payload)->assertCreated();
        $this->postJson('/api/public/stores/dorzak-merchant/orders', $payload)->assertCreated();

        // Same phone -> a single customer row.
        $this->assertSame(1, $this->store->customers()->count());
    }

    public function test_staff_notified_of_online_order(): void
    {
        Notification::fake();
        $p = $this->product(['taxable' => false]);

        $this->postJson('/api/public/stores/dorzak-merchant/orders', [
            'customer' => ['name' => 'Jane', 'phone' => '+1 555-7777'],
            'fulfillment' => 'pickup',
            'items' => [['product_id' => $p->id, 'quantity' => 1]],
        ])->assertCreated();

        Notification::assertSentTo($this->store->users()->first(), OnlineOrderNotification::class);
    }

    public function test_hidden_product_cannot_be_ordered(): void
    {
        Notification::fake();
        $p = $this->product(['show_in_online_store' => false]);

        $this->postJson('/api/public/stores/dorzak-merchant/orders', [
            'customer' => ['name' => 'Jane', 'phone' => '+1 555-7777'],
            'fulfillment' => 'pickup',
            'items' => [['product_id' => $p->id, 'quantity' => 1]],
        ])->assertStatus(422)->assertJsonValidationErrors('items.0.product_id');
    }

    public function test_fawran_order_uses_customer_name_as_reference_when_not_provided(): void
    {
        Notification::fake();
        $this->store->storefrontSetting->update(['fawran_enabled' => true]);
        $p = $this->product(['taxable' => false]);

        $this->postJson('/api/public/stores/dorzak-merchant/orders', [
            'customer' => ['name' => 'Jane Customer', 'phone' => '+1 555-7777'],
            'fulfillment' => 'pickup',
            'payment_method' => 'FAWRAN',
            'items' => [['product_id' => $p->id, 'quantity' => 1]],
        ])->assertCreated();

        $this->assertDatabaseHas('orders', [
            'payment_reference' => 'Jane Customer',
            'payment_method' => 'FAWRAN',
        ]);
    }

    public function test_public_can_fetch_order_status_by_order_number(): void
    {
        Notification::fake();
        $p = $this->product(['price' => 100]);

        $create = $this->postJson('/api/public/stores/dorzak-merchant/orders', [
            'customer' => ['name' => 'Jane', 'phone' => '+1 555-7777'],
            'fulfillment' => 'pickup',
            'items' => [['product_id' => $p->id, 'quantity' => 1]],
        ])->assertCreated();

        $orderNumber = $create->json('data.order_number');

        $this->getJson("/api/public/stores/dorzak-merchant/orders/{$orderNumber}")
            ->assertOk()
            ->assertJsonPath('data.order_number', $orderNumber)
            ->assertJsonPath('data.status', 'CONFIRMING')
            ->assertJsonPath('data.customer_name', 'Jane')
            ->assertJsonPath('data.total', '110.00')
            ->assertJsonCount(1, 'data.items');
    }

    public function test_unknown_order_number_returns_404(): void
    {
        $this->getJson('/api/public/stores/dorzak-merchant/orders/ORD-999999')
            ->assertNotFound();
    }
}
