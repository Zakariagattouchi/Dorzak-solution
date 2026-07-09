<?php

namespace Tests\Feature\Order;

use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderDeliveryFeeTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner();
    }

    private function pendingOrder(array $attrs = []): Order
    {
        return Order::factory()->create(array_merge([
            'store_id' => $this->store->id,
            'fulfillment' => 'DELIVERY',
            'status' => 'CONFIRMING',
            'payment_status' => 'UNPAID',
            'subtotal' => 40,
            'delivery_fee' => 0,
            'total' => 40,
            'delivery_fee_status' => 'PENDING',
        ], $attrs));
    }

    public function test_owner_sets_the_fee_and_the_total_updates(): void
    {
        $order = $this->pendingOrder();

        $this->actingAsMember($this->owner)
            ->patchJson("/api/v1/orders/{$order->id}/delivery-fee", ['delivery_fee' => 12.5])
            ->assertOk()
            ->assertJsonPath('data.delivery_fee', '12.50')
            ->assertJsonPath('data.delivery_fee_status', 'SET')
            ->assertJsonPath('data.total', '52.50');
    }

    public function test_fee_can_be_corrected_while_unpaid(): void
    {
        $order = $this->pendingOrder(['delivery_fee' => 10, 'total' => 50, 'delivery_fee_status' => 'SET']);

        $this->actingAsMember($this->owner)
            ->patchJson("/api/v1/orders/{$order->id}/delivery-fee", ['delivery_fee' => 15])
            ->assertOk()
            ->assertJsonPath('data.total', '55.00');
    }

    public function test_paid_orders_are_immutable(): void
    {
        $order = $this->pendingOrder(['payment_status' => 'PAID']);

        $this->actingAsMember($this->owner)
            ->patchJson("/api/v1/orders/{$order->id}/delivery-fee", ['delivery_fee' => 12])
            ->assertStatus(422);
    }

    public function test_pickup_orders_are_rejected(): void
    {
        $order = $this->pendingOrder(['fulfillment' => 'PICKUP', 'delivery_fee_status' => null]);

        $this->actingAsMember($this->owner)
            ->patchJson("/api/v1/orders/{$order->id}/delivery-fee", ['delivery_fee' => 12])
            ->assertStatus(422);
    }

    public function test_cross_store_order_is_404(): void
    {
        ['store' => $other] = $this->createStoreWithOwner();
        $order = Order::factory()->create([
            'store_id' => $other->id, 'fulfillment' => 'DELIVERY',
            'status' => 'CONFIRMING', 'payment_status' => 'UNPAID',
            'subtotal' => 10, 'delivery_fee' => 0, 'total' => 10,
        ]);

        $this->actingAsMember($this->owner)
            ->patchJson("/api/v1/orders/{$order->id}/delivery-fee", ['delivery_fee' => 12])
            ->assertNotFound();
    }
}
