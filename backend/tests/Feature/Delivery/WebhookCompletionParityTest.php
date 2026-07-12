<?php

namespace Tests\Feature\Delivery;

use App\Enums\OrderStatus;
use App\Events\OrderCompleted;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\DorzakBusinessClient;
use App\Services\LoyaltyService;
use App\Services\OrderService;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Parity contract: a delivery order completed by a Dorzak Business webhook must
 * be indistinguishable from one completed by the merchant through the status API.
 *
 * Both paths must:
 *   - deduct stock from tracked products
 *   - update customer total_orders / total_spent
 *   - accrue loyalty points (when configured)
 *   - set completed_at
 *   - fire OrderCompleted
 */
class WebhookCompletionParityTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('delivery.business', [
            'enabled' => true,
            'base_url' => 'https://delivery.test',
            'client_id' => 'merchant',
            'client_secret' => 'shhh',
            'webhook_secret' => 'hook',
            'timeout' => 8,
            'source_system' => 'dorzak-merchant',
        ]);

        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner([
            'charge_sales_tax' => false,
        ]);
        $this->store->initializeSettings();
        $this->assignPlan($this->store, 'PRO');
        $this->store = $this->store->fresh(['storefrontSetting', 'subscription.plan.featureLimits']);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function deliveryOrderWithItem(Customer $customer, Product $product, int $qty = 2): Order
    {
        app(StoreContext::class)->setMembership(
            $this->owner->memberships()->where('store_id', $this->store->id)->with('store')->first()
        );

        // Create the order at CONFIRMING so we can walk it through transitions.
        $order = app(OrderService::class)->create($this->store, [
            'items' => [['product_id' => $product->id, 'quantity' => $qty]],
            'payment_method' => 'CASH',
            'status' => 'CONFIRMING',
            'fulfillment' => 'DELIVERY',
            'customer_id' => $customer->id,
        ], $this->owner);

        return $order;
    }

    /** Walk an order through CONFIRMING → ACCEPTED → PREPARING → OUT_FOR_DELIVERY using the service. */
    private function walkToOutForDelivery(Order $order): Order
    {
        $service = app(OrderService::class);
        $order = $service->updateStatus($order->fresh(['items', 'customer']), OrderStatus::ACCEPTED, $this->owner);
        $order = $service->updateStatus($order->fresh(['items', 'customer']), OrderStatus::PREPARING, $this->owner);
        $order = $service->updateStatus($order->fresh(['items', 'customer']), OrderStatus::OUT_FOR_DELIVERY, $this->owner);

        return $order->fresh(['items', 'customer']);
    }

    private function postWebhook(string $eventType, array $payload): TestResponse
    {
        $body = json_encode($payload);
        $timestamp = now()->timestamp;
        $signature = DorzakBusinessClient::sign('hook', $eventType, (string) $payload['source_order_id'], $body, $timestamp);

        return $this->call(
            'POST',
            '/api/webhooks/dorzak-business',
            server: [
                'HTTP_X_DORZAK_EVENT_TYPE' => $eventType,
                'HTTP_X_DORZAK_TIMESTAMP' => (string) $timestamp,
                'HTTP_X_DORZAK_SIGNATURE' => $signature,
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: $body,
        );
    }

    // -------------------------------------------------------------------------
    // Parity tests
    // -------------------------------------------------------------------------

    public function test_webhook_completion_deducts_stock_same_as_merchant_completion(): void
    {
        $product = Product::factory()->for($this->store)->create([
            'price' => 50, 'track_stock' => true, 'stock' => 20,
        ]);
        $customer = Customer::factory()->create(['store_id' => $this->store->id]);

        // --- Merchant path ---
        $orderA = $this->walkToOutForDelivery($this->deliveryOrderWithItem($customer, $product, 2));
        $stockAfterA = $product->fresh()->stock; // 20 - 2 = 18 after PREPARING dispatch path

        // Merchant manually completes OUT_FOR_DELIVERY → COMPLETE.
        app(OrderService::class)->updateStatus($orderA, OrderStatus::COMPLETE, $this->owner);
        $stockAfterMerchant = $product->fresh()->stock;

        // Reset stock to same baseline before the second order.
        $product->update(['stock' => $stockAfterA]);

        // --- Webhook path ---
        $orderB = $this->walkToOutForDelivery($this->deliveryOrderWithItem($customer, $product, 2));
        $stockBeforeWebhook = $product->fresh()->stock;

        $this->postWebhook('delivery.status_changed', [
            'source_order_id' => (string) $orderB->id,
            'to' => 'delivered',
        ])->assertOk();

        $stockAfterWebhook = $product->fresh()->stock;

        // Both paths must deduct the same amount.
        $this->assertSame(
            $stockAfterMerchant - $stockAfterA,
            $stockAfterWebhook - $stockBeforeWebhook,
            'Webhook completion must deduct the same stock as merchant completion.'
        );
    }

    public function test_webhook_completion_updates_customer_stats_same_as_merchant_completion(): void
    {
        $product = Product::factory()->for($this->store)->create([
            'price' => 100, 'track_stock' => false,
        ]);

        // Use two independent customers so the paths don't interfere with each other.
        $customerA = Customer::factory()->create([
            'store_id' => $this->store->id, 'total_orders' => 0, 'total_spent' => 0,
        ]);
        $customerB = Customer::factory()->create([
            'store_id' => $this->store->id, 'total_orders' => 0, 'total_spent' => 0,
        ]);

        // --- Merchant path ---
        $orderA = $this->walkToOutForDelivery($this->deliveryOrderWithItem($customerA, $product, 1));
        app(OrderService::class)->updateStatus($orderA->fresh(['items', 'customer']), OrderStatus::COMPLETE, $this->owner);
        $merchantOrders = $customerA->fresh()->total_orders;
        $merchantSpent = (float) $customerA->fresh()->total_spent;

        // --- Webhook path ---
        $orderB = $this->walkToOutForDelivery($this->deliveryOrderWithItem($customerB, $product, 1));
        $this->postWebhook('delivery.status_changed', [
            'source_order_id' => (string) $orderB->id,
            'to' => 'delivered',
        ])->assertOk();

        $this->assertSame($merchantOrders, $customerB->fresh()->total_orders, 'total_orders parity');
        $this->assertEqualsWithDelta($merchantSpent, (float) $customerB->fresh()->total_spent, 0.01, 'total_spent parity');
    }

    public function test_webhook_completion_accrues_loyalty_points(): void
    {
        $product = Product::factory()->for($this->store)->create([
            'price' => 100, 'track_stock' => false,
        ]);
        $customer = Customer::factory()->create(['store_id' => $this->store->id]);

        $loyaltyService = app(LoyaltyService::class);
        app(StoreContext::class)->setStore($this->store);
        $loyaltyService->configure($this->store, [
            'enabled' => true,
            'earn_points_per_currency' => 1,
            'redeem_points' => 100,
            'redeem_value' => 5,
        ]);

        $order = $this->walkToOutForDelivery($this->deliveryOrderWithItem($customer, $product, 1));
        $pointsBefore = $loyaltyService->balance($customer);

        $this->postWebhook('delivery.status_changed', [
            'source_order_id' => (string) $order->id,
            'to' => 'delivered',
        ])->assertOk();

        // 100 total × 1 point per currency unit = 100 points.
        $this->assertGreaterThan($pointsBefore, $loyaltyService->balance($customer), 'Webhook completion must accrue loyalty points.');
    }

    public function test_webhook_completion_fires_order_completed_event(): void
    {
        Event::fake([OrderCompleted::class]);

        $product = Product::factory()->for($this->store)->create([
            'price' => 50, 'track_stock' => false,
        ]);
        $customer = Customer::factory()->create(['store_id' => $this->store->id]);
        $order = $this->walkToOutForDelivery($this->deliveryOrderWithItem($customer, $product, 1));

        $this->postWebhook('delivery.status_changed', [
            'source_order_id' => (string) $order->id,
            'to' => 'delivered',
        ])->assertOk();

        Event::assertDispatched(OrderCompleted::class, fn ($e) => $e->order->id === $order->id);
    }

    public function test_webhook_completion_sets_completed_at(): void
    {
        $product = Product::factory()->for($this->store)->create(['track_stock' => false]);
        $customer = Customer::factory()->create(['store_id' => $this->store->id]);
        $order = $this->walkToOutForDelivery($this->deliveryOrderWithItem($customer, $product, 1));

        $this->assertNull($order->completed_at, 'completed_at must be null before completion.');

        $this->postWebhook('delivery.completed', [
            'source_order_id' => (string) $order->id,
        ])->assertOk();

        $this->assertNotNull($order->fresh()->completed_at, 'Webhook must set completed_at.');
    }

    public function test_webhook_completion_is_idempotent(): void
    {
        Event::fake([OrderCompleted::class]);

        $product = Product::factory()->for($this->store)->create([
            'price' => 50, 'track_stock' => true, 'stock' => 10,
        ]);
        $customer = Customer::factory()->create(['store_id' => $this->store->id]);
        $order = $this->walkToOutForDelivery($this->deliveryOrderWithItem($customer, $product, 1));

        $payload = ['source_order_id' => (string) $order->id, 'to' => 'delivered'];
        $this->postWebhook('delivery.status_changed', $payload)->assertOk();
        $this->postWebhook('delivery.status_changed', $payload)->assertOk();

        // OrderCompleted fires exactly once.
        Event::assertDispatchedTimes(OrderCompleted::class, 1);

        // Stock deducted exactly once (10 - 1 = 9).
        $this->assertSame(9, $product->fresh()->stock, 'Stock must only be deducted once on repeated webhooks.');
    }

    public function test_out_of_order_delivered_after_already_complete_is_ignored(): void
    {
        $product = Product::factory()->for($this->store)->create([
            'price' => 50, 'track_stock' => true, 'stock' => 10,
        ]);
        $customer = Customer::factory()->create(['store_id' => $this->store->id]);
        $order = $this->walkToOutForDelivery($this->deliveryOrderWithItem($customer, $product, 1));

        // Complete via merchant first.
        app(OrderService::class)->updateStatus($order->fresh(['items', 'customer']), OrderStatus::COMPLETE, $this->owner);
        $stockAfterFirstCompletion = $product->fresh()->stock;

        // A stale 'delivered' webhook must not re-run side-effects.
        $this->postWebhook('delivery.status_changed', [
            'source_order_id' => (string) $order->id,
            'to' => 'delivered',
        ])->assertOk();

        $this->assertSame($stockAfterFirstCompletion, $product->fresh()->stock, 'Stock must not be double-deducted.');
    }
}
