<?php

namespace Tests\Feature\Referral;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Referral;
use App\Models\ReferralProgram;
use App\Models\Store;
use App\Services\ReferralService;
use App\Services\WalletService;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Premium feature: referral codes reward both sides in store credit. */
class ReferralServiceTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        $this->store = Store::factory()->create();
        app(StoreContext::class)->setStore($this->store);
        // Create the program directly to stay off the plan gate (covered by API test).
        ReferralProgram::create([
            'store_id' => $this->store->id, 'enabled' => true, 'referrer_reward' => 15, 'referee_reward' => 5,
        ]);
    }

    public function test_a_customer_gets_a_referral_code(): void
    {
        $customer = Customer::factory()->create(['store_id' => $this->store->id]);

        $code = app(ReferralService::class)->codeFor($customer);

        $this->assertNotEmpty($code);
        $this->assertSame($code, app(ReferralService::class)->codeFor($customer)); // stable
    }

    public function test_first_order_with_a_code_rewards_both_in_store_credit(): void
    {
        $referrer = Customer::factory()->create(['store_id' => $this->store->id]);
        $referred = Customer::factory()->create(['store_id' => $this->store->id]);
        $code = app(ReferralService::class)->codeFor($referrer);

        app(ReferralService::class)->attribute($this->store, $referred, $code, 0);
        $order = Order::factory()->create(['store_id' => $this->store->id, 'customer_id' => $referred->id]);
        app(ReferralService::class)->rewardOnOrder($order);

        $this->assertSame('15.00', number_format(app(WalletService::class)->balance($referrer), 2));
        $this->assertSame('5.00', number_format(app(WalletService::class)->balance($referred), 2));
        $this->assertDatabaseHas('referrals', ['referred_customer_id' => $referred->id, 'status' => 'rewarded']);
    }

    public function test_self_referral_is_ignored(): void
    {
        $customer = Customer::factory()->create(['store_id' => $this->store->id]);
        $code = app(ReferralService::class)->codeFor($customer);

        app(ReferralService::class)->attribute($this->store, $customer, $code, 0);

        $this->assertDatabaseCount('referrals', 0);
    }

    public function test_returning_customer_cannot_be_referred(): void
    {
        $referrer = Customer::factory()->create(['store_id' => $this->store->id]);
        $returning = Customer::factory()->create(['store_id' => $this->store->id]);
        $code = app(ReferralService::class)->codeFor($referrer);

        app(ReferralService::class)->attribute($this->store, $returning, $code, 3); // has prior orders

        $this->assertDatabaseCount('referrals', 0);
    }

    public function test_a_referred_customer_is_only_attributed_once(): void
    {
        $referrer = Customer::factory()->create(['store_id' => $this->store->id]);
        $referred = Customer::factory()->create(['store_id' => $this->store->id]);
        $code = app(ReferralService::class)->codeFor($referrer);

        app(ReferralService::class)->attribute($this->store, $referred, $code, 0);
        app(ReferralService::class)->attribute($this->store, $referred, $code, 0);

        $this->assertSame(1, Referral::where('referred_customer_id', $referred->id)->count());
    }
}
