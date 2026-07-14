<?php

namespace Tests\Feature\Loyalty;

use App\Exceptions\DomainConflictException;
use App\Models\Customer;
use App\Models\LoyaltyAccount;
use App\Models\Store;
use App\Services\LoyaltyService;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Premium feature: redeeming loyalty points for a checkout discount. */
class LoyaltyRedemptionTest extends TestCase
{
    use RefreshDatabase;

    private function storeWithProgram(): Store
    {
        $store = Store::factory()->create();
        app(StoreContext::class)->setStore($store);
        app(LoyaltyService::class)->configure($store, [
            'enabled' => true,
            'earn_points_per_currency' => 1,
            'redeem_points' => 100,
            'redeem_value' => 5,
        ]);

        return $store;
    }

    private function customerWithPoints(Store $store, int $points): Customer
    {
        $customer = Customer::factory()->create(['store_id' => $store->id]);
        LoyaltyService::class; // service resolves below
        LoyaltyAccount::create([
            'store_id' => $store->id, 'customer_id' => $customer->id, 'points' => $points,
        ]);

        return $customer;
    }

    public function test_redeeming_points_returns_a_discount_and_debits_the_balance(): void
    {
        $store = $this->storeWithProgram();
        $customer = $this->customerWithPoints($store, 250);

        // 200 points = 2 redemption units of 100 → 2 * 5 = 10.00 discount.
        $discount = app(LoyaltyService::class)->redeem($customer, 200);

        $this->assertSame('10.00', number_format($discount, 2));
        $this->assertSame(50, app(LoyaltyService::class)->balance($customer));
    }

    public function test_redeeming_more_than_the_balance_is_refused(): void
    {
        $store = $this->storeWithProgram();
        $customer = $this->customerWithPoints($store, 50);

        $this->expectException(DomainConflictException::class);
        app(LoyaltyService::class)->redeem($customer, 100);
    }
}
