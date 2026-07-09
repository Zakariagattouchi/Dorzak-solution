<?php

namespace Tests\Feature\Order;

use App\Enums\StaffRole;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\OrderService;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderListAndStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner([
            'charge_sales_tax' => true, 'tax_rate' => 10,
        ]);
    }

    /** Create an order through the real service (binds store context first). */
    private function makeOrder(array $data): Order
    {
        app(StoreContext::class)->setMembership(
            $this->owner->memberships()->where('store_id', $this->store->id)->with('store')->first()
        );

        return app(OrderService::class)->create($this->store, $data, $this->owner);
    }

    public function test_list_filters_by_status_and_returns_summary(): void
    {
        $p = Product::factory()->for($this->store)->create(['price' => 100, 'stock' => 100, 'taxable' => true]);
        $this->makeOrder(['items' => [['product_id' => $p->id, 'quantity' => 1]], 'payment_method' => 'CARD', 'status' => 'COMPLETE']);
        $this->makeOrder(['items' => [['product_id' => $p->id, 'quantity' => 1]], 'payment_method' => 'TRANSFER', 'status' => 'CONFIRMING']);

        $this->actingAsMember($this->owner)
            ->getJson('/api/v1/orders')
            ->assertOk()
            ->assertJsonPath('meta.summary.completed_count', 1)
            ->assertJsonPath('meta.summary.pending_count', 1)
            ->assertJsonPath('meta.summary.revenue', '220.00'); // 110 completed + 110 pending (both non-cancelled)

        $this->actingAsMember($this->owner)
            ->getJson('/api/v1/orders?status=COMPLETE')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_payment_method_filter(): void
    {
        $p = Product::factory()->for($this->store)->create(['stock' => 100]);
        $this->makeOrder(['items' => [['product_id' => $p->id, 'quantity' => 1]], 'payment_method' => 'CASH']);
        $this->makeOrder(['items' => [['product_id' => $p->id, 'quantity' => 1]], 'payment_method' => 'CARD']);

        $this->actingAsMember($this->owner)
            ->getJson('/api/v1/orders?payment_method=CASH')
            ->assertJsonCount(1, 'data');
    }

    public function test_show_includes_receipt_block(): void
    {
        $this->store->receiptSetting->update(['header' => 'Thanks!', 'footer' => 'Come again']);
        $p = Product::factory()->for($this->store)->create(['stock' => 100]);
        $order = $this->makeOrder(['items' => [['product_id' => $p->id, 'quantity' => 1]], 'payment_method' => 'CASH']);

        $this->actingAsMember($this->owner)
            ->getJson("/api/v1/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.receipt.business_name', $this->store->name)
            ->assertJsonPath('data.receipt.header', 'Thanks!');
    }

    public function test_completing_pending_deducts_stock_and_updates_counters(): void
    {
        $customer = Customer::factory()->for($this->store)->create(['total_orders' => 0, 'total_spent' => 0]);
        $p = Product::factory()->for($this->store)->create(['price' => 100, 'stock' => 20, 'taxable' => true]);
        $order = $this->makeOrder([
            'items' => [['product_id' => $p->id, 'quantity' => 2]],
            'customer_id' => $customer->id, 'payment_method' => 'TRANSFER', 'status' => 'CONFIRMING',
        ]);

        $this->assertSame(20, $p->fresh()->stock); // pending -> no deduction yet

        foreach (['ACCEPTED', 'PREPARING', 'COMPLETE'] as $status) {
            $this->actingAsMember($this->owner)
                ->patchJson("/api/v1/orders/{$order->id}/status", ['status' => $status])
                ->assertOk();
        }
        $this->assertSame('COMPLETE', $order->fresh()->status->value);

        $this->assertSame(18, $p->fresh()->stock);
        $this->assertSame(1, $customer->fresh()->total_orders);
    }

    public function test_cancelling_completed_restores_stock_and_reverses_counters(): void
    {
        $customer = Customer::factory()->for($this->store)->create(['total_orders' => 0, 'total_spent' => 0]);
        $p = Product::factory()->for($this->store)->create(['price' => 100, 'stock' => 20, 'taxable' => true]);
        $order = $this->makeOrder([
            'items' => [['product_id' => $p->id, 'quantity' => 3]],
            'customer_id' => $customer->id, 'payment_method' => 'CARD', 'status' => 'COMPLETE',
        ]);

        $this->assertSame(17, $p->fresh()->stock);
        $this->assertSame(1, $customer->fresh()->total_orders);

        $this->actingAsMember($this->owner)
            ->patchJson("/api/v1/orders/{$order->id}/status", ['status' => 'CANCELLED'])
            ->assertOk()
            ->assertJsonPath('data.status', 'CANCELLED');

        $this->assertSame(20, $p->fresh()->stock); // restored
        $this->assertSame(0, $customer->fresh()->total_orders);
        $this->assertDatabaseHas('stock_movements', ['order_id' => $order->id, 'type' => 'CANCEL_RETURN']);
    }

    public function test_cancelling_pending_does_not_touch_stock(): void
    {
        $p = Product::factory()->for($this->store)->create(['stock' => 20]);
        $order = $this->makeOrder(['items' => [['product_id' => $p->id, 'quantity' => 2]], 'payment_method' => 'CASH', 'status' => 'CONFIRMING']);

        $this->actingAsMember($this->owner)
            ->patchJson("/api/v1/orders/{$order->id}/status", ['status' => 'CANCELLED'])
            ->assertOk();

        $this->assertSame(20, $p->fresh()->stock);
    }

    public function test_invalid_transition_is_rejected(): void
    {
        $p = Product::factory()->for($this->store)->create(['stock' => 20]);
        $order = $this->makeOrder(['items' => [['product_id' => $p->id, 'quantity' => 1]], 'payment_method' => 'CASH', 'status' => 'COMPLETE']);
        // COMPLETE -> COMPLETE is not a valid transition.
        $this->actingAsMember($this->owner)
            ->patchJson("/api/v1/orders/{$order->id}/status", ['status' => 'COMPLETE'])
            ->assertStatus(422)->assertJsonValidationErrors('status');
    }

    public function test_cashier_cannot_change_status(): void
    {
        $cashier = $this->createMember(StaffRole::CASHIER, $this->store);
        $p = Product::factory()->for($this->store)->create(['stock' => 20]);
        $order = $this->makeOrder(['items' => [['product_id' => $p->id, 'quantity' => 1]], 'payment_method' => 'CASH', 'status' => 'CONFIRMING']);

        $this->actingAsMember($cashier)
            ->patchJson("/api/v1/orders/{$order->id}/status", ['status' => 'COMPLETE'])
            ->assertForbidden();
    }

    public function test_viewer_can_list_orders(): void
    {
        $viewer = $this->createMember(StaffRole::VIEWER, $this->store);

        $this->actingAsMember($viewer)->getJson('/api/v1/orders')->assertOk();
    }

    public function test_export_returns_csv(): void
    {
        $p = Product::factory()->for($this->store)->create(['stock' => 100]);
        $order = $this->makeOrder(['items' => [['product_id' => $p->id, 'quantity' => 1]], 'payment_method' => 'CASH']);

        $response = $this->actingAsMember($this->owner)->get('/api/v1/orders/export');
        $response->assertOk();
        $this->assertStringContainsString('order_number', $response->streamedContent());
        $this->assertStringContainsString($order->order_number, $response->streamedContent());
    }

    public function test_cross_tenant_order_is_not_found(): void
    {
        $foreign = Order::factory()->create();

        $this->actingAsMember($this->owner)
            ->getJson("/api/v1/orders/{$foreign->id}")
            ->assertNotFound();
    }
}
