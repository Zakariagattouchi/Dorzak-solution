<?php

namespace Tests\Feature\Order;

use App\Enums\StaffRole;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CreateOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner([
            'charge_sales_tax' => true, 'tax_rate' => 10, 'tax_included_in_price' => false,
        ]);
    }

    private function product(array $attrs = []): Product
    {
        return Product::factory()->for($this->store)->create(array_merge([
            'price' => 100, 'cost' => 40, 'taxable' => true, 'track_stock' => true, 'stock' => 50, 'min_stock' => 5,
        ], $attrs));
    }

    public function test_creates_order_and_recomputes_totals_server_side(): void
    {
        $p = $this->product(['price' => 100]);

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/orders', [
                'items' => [['product_id' => $p->id, 'quantity' => 2]],
                'payment_method' => 'CARD',
                'subtotal' => 999, 'total' => 999, // client totals must be ignored
            ])
            ->assertCreated()
            ->assertJsonPath('data.subtotal', '200.00')
            ->assertJsonPath('data.tax_amount', '20.00')
            ->assertJsonPath('data.total', '220.00')
            ->assertJsonPath('data.status', 'COMPLETE')
            ->assertJsonPath('data.order_number', 'ORD-1000');
    }

    public function test_empty_cart_is_rejected(): void
    {
        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/orders', ['items' => [], 'payment_method' => 'CASH'])
            ->assertStatus(422)->assertJsonValidationErrors('items');
    }

    public function test_disabled_payment_method_is_rejected(): void
    {
        $this->store->update(['accepted_payment_methods' => ['CASH']]);
        $p = $this->product();

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/orders', [
                'items' => [['product_id' => $p->id, 'quantity' => 1]], 'payment_method' => 'CARD',
            ])
            ->assertStatus(422)->assertJsonValidationErrors('payment_method');
    }

    public function test_walk_in_order_snapshots_customer_name(): void
    {
        $p = $this->product();

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/orders', [
                'items' => [['product_id' => $p->id, 'quantity' => 1]], 'payment_method' => 'CASH',
            ])
            ->assertCreated()
            ->assertJsonPath('data.customer_name', 'Walk-in Customer');
    }

    public function test_stock_deducted_and_sale_movement_written(): void
    {
        $p = $this->product(['stock' => 50]);

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/orders', [
                'items' => [['product_id' => $p->id, 'quantity' => 3]], 'payment_method' => 'CASH',
            ])
            ->assertCreated();

        $this->assertSame(47, $p->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $p->id, 'type' => 'SALE', 'quantity_change' => -3, 'stock_after' => 47]);
    }

    public function test_insufficient_stock_returns_409_with_details(): void
    {
        $p = $this->product(['stock' => 2]);

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/orders', [
                'items' => [['product_id' => $p->id, 'quantity' => 5]], 'payment_method' => 'CASH',
            ])
            ->assertStatus(409)
            ->assertJsonPath('code', 'INSUFFICIENT_STOCK')
            ->assertJsonPath('details.0.product_id', $p->id)
            ->assertJsonPath('details.0.available', 2);

        $this->assertSame(2, $p->fresh()->stock); // unchanged
        $this->assertDatabaseCount('orders', 0);  // transaction rolled back
    }

    public function test_customer_counters_increment(): void
    {
        $p = $this->product(['price' => 100]);
        $customer = Customer::factory()->for($this->store)->create(['total_orders' => 0, 'total_spent' => 0]);

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/orders', [
                'items' => [['product_id' => $p->id, 'quantity' => 1]],
                'customer_id' => $customer->id, 'payment_method' => 'CARD',
            ])
            ->assertCreated();

        $customer->refresh();
        $this->assertSame(1, $customer->total_orders);
        $this->assertSame('110.00', $customer->total_spent); // 100 + 10% tax
    }

    public function test_variant_price_used_and_variant_stock_deducted(): void
    {
        $p = $this->product(['price' => 100]);
        $variant = $p->variants()->create(['name' => 'Large', 'price' => 120, 'stock' => 10, 'sort_order' => 0]);
        $p->update(['stock' => 10]);

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/orders', [
                'items' => [['product_id' => $p->id, 'variant_id' => $variant->id, 'quantity' => 2]],
                'payment_method' => 'CASH',
            ])
            ->assertCreated()
            ->assertJsonPath('data.subtotal', '240.00'); // 120 * 2

        $this->assertSame(8, $variant->fresh()->stock);
        $this->assertSame(8, $p->fresh()->stock); // parent mirrors variant sum
    }

    public function test_non_taxable_items_excluded_from_tax(): void
    {
        $taxable = $this->product(['price' => 100, 'taxable' => true]);
        $exempt = $this->product(['price' => 50, 'taxable' => false]);

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/orders', [
                'items' => [
                    ['product_id' => $taxable->id, 'quantity' => 1],
                    ['product_id' => $exempt->id, 'quantity' => 1],
                ],
                'payment_method' => 'CARD',
            ])
            ->assertCreated()
            ->assertJsonPath('data.subtotal', '150.00')
            ->assertJsonPath('data.tax_amount', '10.00') // only the 100 line at 10%
            ->assertJsonPath('data.total', '160.00');
    }

    public function test_low_stock_notification_sent_when_threshold_crossed(): void
    {
        Notification::fake();
        $p = $this->product(['stock' => 6, 'min_stock' => 5]);

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/orders', [
                'items' => [['product_id' => $p->id, 'quantity' => 2]], 'payment_method' => 'CASH',
            ])
            ->assertCreated(); // stock 6 -> 4 (<= min 5)

        Notification::assertSentTo($this->owner, LowStockNotification::class);
    }

    public function test_order_number_is_sequential_per_store(): void
    {
        $p1 = $this->product();
        $this->actingAsMember($this->owner)->postJson('/api/v1/orders', ['items' => [['product_id' => $p1->id, 'quantity' => 1]], 'payment_method' => 'CASH'])
            ->assertJsonPath('data.order_number', 'ORD-1000');
        $this->actingAsMember($this->owner)->postJson('/api/v1/orders', ['items' => [['product_id' => $p1->id, 'quantity' => 1]], 'payment_method' => 'CASH'])
            ->assertJsonPath('data.order_number', 'ORD-1001');

        // A second store starts its own sequence.
        $otherStore = Store::factory()->create();
        $otherOwner = $this->createMember(StaffRole::OWNER, $otherStore);
        $op = Product::factory()->for($otherStore)->create(['stock' => 10]);
        $this->actingAsMember($otherOwner)->postJson('/api/v1/orders', ['items' => [['product_id' => $op->id, 'quantity' => 1]], 'payment_method' => 'CASH'])
            ->assertJsonPath('data.order_number', 'ORD-1000');
    }

    public function test_viewer_cannot_create_order(): void
    {
        $viewer = $this->createMember(StaffRole::VIEWER, $this->store);
        $p = $this->product();

        $this->actingAsMember($viewer)
            ->postJson('/api/v1/orders', ['items' => [['product_id' => $p->id, 'quantity' => 1]], 'payment_method' => 'CASH'])
            ->assertForbidden();
    }
}
