<?php

namespace Tests\Feature\Coupon;

use App\Exceptions\DomainConflictException;
use App\Models\Coupon;
use App\Models\Store;
use App\Services\CouponService;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Premium feature: customer-facing coupon codes with limits, min order, expiry. */
class CouponServiceTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        $this->store = Store::factory()->create();
        app(StoreContext::class)->setStore($this->store);
    }

    private function coupon(array $attrs = []): Coupon
    {
        return Coupon::create(array_merge([
            'store_id' => $this->store->id,
            'code' => 'SAVE10',
            'type' => 'PERCENT',
            'value' => 10,
            'min_order' => 0,
            'usage_limit' => null,
            'used_count' => 0,
            'active' => true,
        ], $attrs));
    }

    public function test_percent_coupon_discounts_the_subtotal(): void
    {
        $this->coupon(['type' => 'PERCENT', 'value' => 10]);

        $quote = app(CouponService::class)->quote($this->store, 'SAVE10', 100);

        $this->assertSame('10.00', number_format($quote['discount'], 2));
    }

    public function test_fixed_coupon_discounts_a_flat_amount_capped_at_subtotal(): void
    {
        $this->coupon(['code' => 'FLAT20', 'type' => 'FIXED', 'value' => 20]);

        $this->assertSame('20.00', number_format(app(CouponService::class)->quote($this->store, 'FLAT20', 100)['discount'], 2));
        // Never more than the order.
        $this->assertSame('15.00', number_format(app(CouponService::class)->quote($this->store, 'FLAT20', 15)['discount'], 2));
    }

    public function test_below_minimum_order_is_refused(): void
    {
        $this->coupon(['min_order' => 50]);

        $this->expectException(DomainConflictException::class);
        app(CouponService::class)->quote($this->store, 'SAVE10', 30);
    }

    public function test_exhausted_usage_limit_is_refused(): void
    {
        $this->coupon(['usage_limit' => 5, 'used_count' => 5]);

        $this->expectException(DomainConflictException::class);
        app(CouponService::class)->quote($this->store, 'SAVE10', 100);
    }

    public function test_expired_coupon_is_refused(): void
    {
        $this->coupon(['expires_at' => now()->subDay()]);

        $this->expectException(DomainConflictException::class);
        app(CouponService::class)->quote($this->store, 'SAVE10', 100);
    }

    public function test_unknown_code_is_refused(): void
    {
        $this->expectException(DomainConflictException::class);
        app(CouponService::class)->quote($this->store, 'NOPE', 100);
    }
}
