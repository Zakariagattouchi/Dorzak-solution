<?php

namespace Tests\Feature\Loyalty;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Store;
use App\Services\LoyaltyService;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Premium feature: points-per-spend loyalty. Points accrue on completed orders
 * and are read back as a customer's balance.
 */
class LoyaltyAccrualTest extends TestCase
{
    use RefreshDatabase;

    private function store(): Store
    {
        $store = Store::factory()->create();
        app(StoreContext::class)->setStore($store);

        return $store;
    }

    public function test_points_accrue_for_a_completed_order(): void
    {
        $store = $this->store();
        app(LoyaltyService::class)->configure($store, [
            'enabled' => true,
            'earn_points_per_currency' => 2,
            'redeem_points' => 100,
            'redeem_value' => 5,
        ]);
        $customer = Customer::factory()->create(['store_id' => $store->id]);
        $order = Order::factory()->create([
            'store_id' => $store->id,
            'customer_id' => $customer->id,
            'total' => 25,
        ]);

        app(LoyaltyService::class)->accrueForOrder($order);

        // 25 spent * 2 points per unit = 50.
        $this->assertSame(50, app(LoyaltyService::class)->balance($customer));
    }
}
