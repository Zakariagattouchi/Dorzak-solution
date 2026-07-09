<?php

namespace Tests\Feature\Onboarding;

use App\Enums\PlanFeature;
use App\Enums\StaffRole;
use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Store;
use App\Services\PlanGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SelfServeOnboardingTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // Signup lands on the free plan with a working tenant
    // -----------------------------------------------------------------------

    public function test_register_creates_tenant_on_default_free_plan(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'New Merchant',
            'email' => 'new@merchant.test',
            'password' => 'super-secret-1',
            'password_confirmation' => 'super-secret-1',
            'business_name' => 'Fresh Bites',
            'device_name' => 'web',
        ])->assertCreated();

        $this->assertNotNull($response->json('data.token'));

        $store = Store::where('name', 'Fresh Bites')->first();
        $this->assertNotNull($store);
        $this->assertSame(32, strlen($store->menu_token));
        $this->assertTrue($store->subscription->plan->is_default);
    }

    // -----------------------------------------------------------------------
    // Plans catalog
    // -----------------------------------------------------------------------

    public function test_members_can_list_active_plans_with_trial_days(): void
    {
        ['user' => $owner] = $this->createStoreWithOwner();

        $response = $this->actingAsMember($owner)
            ->getJson('/api/v1/plans')
            ->assertOk();

        $codes = collect($response->json('data'))->pluck('code');
        $this->assertContains('FREE', $codes->all());
        $this->assertContains('PRO', $codes->all());

        $pro = collect($response->json('data'))->firstWhere('code', 'PRO');
        $this->assertSame(14, $pro['trial_days']);
    }

    public function test_inactive_plans_are_hidden_from_the_catalog(): void
    {
        ['user' => $owner] = $this->createStoreWithOwner();
        Plan::where('code', 'ENTERPRISE')->update(['is_active' => false]);

        $response = $this->actingAsMember($owner)->getJson('/api/v1/plans')->assertOk();

        $this->assertNotContains('ENTERPRISE', collect($response->json('data'))->pluck('code')->all());
    }

    // -----------------------------------------------------------------------
    // Free trial
    // -----------------------------------------------------------------------

    public function test_owner_can_start_a_trial_and_features_unlock(): void
    {
        ['user' => $owner, 'store' => $store] = $this->createStoreWithOwner();
        $pro = Plan::where('code', 'PRO')->first();

        $this->assertFalse(app(PlanGate::class)->allows($store, PlanFeature::ONLINE_ORDERING));

        $this->actingAsMember($owner)
            ->postJson('/api/v1/subscription/trial', ['plan_id' => $pro->id])
            ->assertOk()
            ->assertJsonPath('data.plan', 'PRO')
            ->assertJsonPath('data.status', 'TRIALING')
            ->assertJsonPath('data.trial_used', true);

        $store->refresh();
        app(PlanGate::class)->forget($store);
        $this->assertTrue(app(PlanGate::class)->allows($store, PlanFeature::ONLINE_ORDERING));
        $this->assertNotNull($store->subscription->trial_ends_at);
    }

    public function test_trial_cannot_be_used_twice(): void
    {
        ['user' => $owner, 'store' => $store] = $this->createStoreWithOwner();
        $pro = Plan::where('code', 'PRO')->first();

        $store->subscription->update(['trial_used_at' => now()->subMonth()]);

        $this->actingAsMember($owner)
            ->postJson('/api/v1/subscription/trial', ['plan_id' => $pro->id])
            ->assertStatus(422);
    }

    public function test_default_plan_offers_no_trial(): void
    {
        ['user' => $owner] = $this->createStoreWithOwner();
        $free = Plan::where('code', 'FREE')->first();

        $this->actingAsMember($owner)
            ->postJson('/api/v1/subscription/trial', ['plan_id' => $free->id])
            ->assertStatus(422);
    }

    public function test_non_owner_cannot_start_a_trial(): void
    {
        $cashier = $this->createMember(StaffRole::CASHIER);
        $pro = Plan::where('code', 'PRO')->first();

        $this->actingAsMember($cashier)
            ->postJson('/api/v1/subscription/trial', ['plan_id' => $pro->id])
            ->assertForbidden();
    }

    public function test_paid_store_cannot_start_a_trial(): void
    {
        ['user' => $owner, 'store' => $store] = $this->createStoreWithOwner();
        $this->assignPlan($store, 'ENTERPRISE');
        $pro = Plan::where('code', 'PRO')->first();

        $this->actingAsMember($owner)
            ->postJson('/api/v1/subscription/trial', ['plan_id' => $pro->id])
            ->assertStatus(422);
    }

    // -----------------------------------------------------------------------
    // Trial expiry
    // -----------------------------------------------------------------------

    public function test_expire_trials_command_reverts_to_default_plan(): void
    {
        ['store' => $store] = $this->createStoreWithOwner();
        $pro = Plan::where('code', 'PRO')->first();

        $store->subscription->update([
            'plan_id' => $pro->id,
            'status' => SubscriptionStatus::TRIALING,
            'trial_ends_at' => now()->subDay(),
            'trial_used_at' => now()->subDays(15),
        ]);

        $this->artisan('subscriptions:expire-trials')->assertSuccessful();

        $subscription = $store->subscription->fresh();
        $this->assertTrue($subscription->plan->is_default);
        $this->assertSame(SubscriptionStatus::ACTIVE, $subscription->status);
        $this->assertNull($subscription->trial_ends_at);
        $this->assertNotNull($subscription->trial_used_at); // trial stays burned
    }

    public function test_expire_trials_leaves_running_trials_alone(): void
    {
        ['store' => $store] = $this->createStoreWithOwner();
        $pro = Plan::where('code', 'PRO')->first();

        $store->subscription->update([
            'plan_id' => $pro->id,
            'status' => SubscriptionStatus::TRIALING,
            'trial_ends_at' => now()->addDays(5),
            'trial_used_at' => now()->subDays(9),
        ]);

        $this->artisan('subscriptions:expire-trials')->assertSuccessful();

        $subscription = $store->subscription->fresh();
        $this->assertSame('PRO', $subscription->plan->code);
        $this->assertSame(SubscriptionStatus::TRIALING, $subscription->status);
    }
}
