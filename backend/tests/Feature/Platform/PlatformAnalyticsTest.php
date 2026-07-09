<?php

namespace Tests\Feature\Platform;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_platform_admin' => true]);
    }

    private function seedSale(int $storeId, string $product, int $qty, float $total): Order
    {
        $customer = Customer::factory()->create(['store_id' => $storeId, 'total_spent' => $total, 'total_orders' => 1]);
        $order = Order::factory()->create([
            'store_id' => $storeId,
            'customer_id' => $customer->id,
            'status' => 'COMPLETE',
            'total' => $total,
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_name' => $product,
            'unit_price' => $total / max($qty, 1),
            'quantity' => $qty,
            'line_total' => $total,
        ]);

        return $order;
    }

    public function test_platform_analytics_reports_gmv_top_stores_and_trending(): void
    {
        ['store' => $storeA] = $this->createStoreWithOwner();
        ['store' => $storeB] = $this->createStoreWithOwner();
        Product::factory()->create(['store_id' => $storeA->id]);

        $this->seedSale($storeA->id, 'Latte', 5, 100);
        $this->seedSale($storeB->id, 'Latte', 2, 40);
        $this->seedSale($storeA->id, 'Muffin', 1, 10);

        $response = $this->actingAsMember($this->admin)
            ->getJson('/api/v1/platform/analytics')
            ->assertOk();

        $this->assertEquals(150.0, $response->json('data.gmv'));
        $this->assertSame(3, $response->json('data.orders'));

        // Latte (7 units) trends above Muffin (1 unit).
        $trending = $response->json('data.trending_products');
        $this->assertSame('Latte', $trending[0]['name']);
        $this->assertSame(7, $trending[0]['qty']);

        // Store A (110) tops Store B (40).
        $this->assertSame($storeA->id, $response->json('data.top_stores.0.id'));
    }

    public function test_store_analytics_returns_customers_orders_and_trending(): void
    {
        ['store' => $store] = $this->createStoreWithOwner();
        $this->seedSale($store->id, 'Croissant', 4, 48);

        $response = $this->actingAsMember($this->admin)
            ->getJson("/api/v1/platform/stores/{$store->id}/analytics")
            ->assertOk();

        $this->assertEquals(48.0, $response->json('data.revenue'));
        $this->assertSame(1, $response->json('data.orders'));
        $this->assertSame('Croissant', $response->json('data.trending_products.0.name'));
        $this->assertNotEmpty($response->json('data.recent_orders'));
        $this->assertNotEmpty($response->json('data.top_customers'));
        $this->assertCount(30, $response->json('data.revenue_last_30_days'));
    }

    public function test_regular_user_cannot_see_platform_analytics(): void
    {
        ['user' => $owner, 'store' => $store] = $this->createStoreWithOwner();

        $this->actingAsMember($owner)->getJson('/api/v1/platform/analytics')->assertForbidden();
        $this->actingAsMember($owner)->getJson("/api/v1/platform/stores/{$store->id}/analytics")->assertForbidden();
    }
}
