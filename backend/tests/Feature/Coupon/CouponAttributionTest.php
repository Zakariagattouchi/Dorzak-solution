<?php

namespace Tests\Feature\Coupon;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\CouponService;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Marketing depth: orders snapshot the coupon that discounted them, so the
 * console can answer "how many orders and how much revenue did SAVE10 drive?".
 */
class CouponAttributionTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner(['charge_sales_tax' => false]);
        $this->assignPlan($this->store, 'PRO');
        app(StoreContext::class)->setStore($this->store);
        app(CouponService::class)->create($this->store, ['code' => 'SAVE10', 'type' => 'PERCENT', 'value' => 10]);
    }

    public function test_an_order_snapshots_the_coupon_that_discounted_it(): void
    {
        $product = Product::factory()->for($this->store)->create(['price' => 100, 'track_stock' => false]);

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/orders', [
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
                'payment_method' => 'CARD',
                'coupon_code' => 'SAVE10',
            ])
            ->assertCreated();

        $coupon = Coupon::firstWhere('code', 'SAVE10');
        $order = Order::sole();
        $this->assertSame($coupon->id, $order->coupon_id);
        $this->assertSame('SAVE10', $order->coupon_code);
    }

    public function test_coupon_stats_report_orders_revenue_and_discount(): void
    {
        $product = Product::factory()->for($this->store)->create(['price' => 100, 'track_stock' => false]);

        foreach ([1, 2] as $qty) {
            $this->actingAsMember($this->owner)
                ->postJson('/api/v1/orders', [
                    'items' => [['product_id' => $product->id, 'quantity' => $qty]],
                    'payment_method' => 'CARD',
                    'coupon_code' => 'SAVE10',
                ])->assertCreated();
        }

        $coupon = Coupon::firstWhere('code', 'SAVE10');
        $stats = app(CouponService::class)->stats($coupon);

        $this->assertSame(2, $stats['orders']);
        // 100 + 200 gross, 10% off each → 270 revenue, 30 discount.
        $this->assertSame('270.00', number_format($stats['revenue'], 2));
        $this->assertSame('30.00', number_format($stats['discount_given'], 2));
    }
}
