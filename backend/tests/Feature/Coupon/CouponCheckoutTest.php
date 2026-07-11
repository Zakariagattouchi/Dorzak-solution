<?php

namespace Tests\Feature\Coupon;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\CouponService;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Premium feature: a coupon code applied at checkout discounts and is redeemed. */
class CouponCheckoutTest extends TestCase
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

    public function test_a_coupon_code_discounts_and_is_redeemed(): void
    {
        $product = Product::factory()->for($this->store)->create(['price' => 100, 'track_stock' => false]);

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/orders', [
                'items' => [['product_id' => $product->id, 'quantity' => 2]],
                'payment_method' => 'CARD',
                'coupon_code' => 'SAVE10',
            ])
            ->assertCreated()
            ->assertJsonPath('data.discount', '20.00')  // 10% of 200
            ->assertJsonPath('data.total', '180.00');

        $this->assertSame(1, Coupon::firstWhere('code', 'SAVE10')->used_count);
    }
}
