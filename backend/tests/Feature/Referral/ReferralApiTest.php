<?php

namespace Tests\Feature\Referral;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Premium feature: referral config API gated by PlanFeature::REFERRALS. */
class ReferralApiTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner();
    }

    public function test_free_plan_cannot_configure_referrals(): void
    {
        $this->assignPlan($this->store, 'FREE');

        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/referrals', ['enabled' => true, 'referrer_reward' => 15, 'referee_reward' => 5])
            ->assertStatus(402);
    }

    public function test_pro_plan_configures_referrals(): void
    {
        $this->assignPlan($this->store, 'PRO');

        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/referrals', ['enabled' => true, 'referrer_reward' => 20, 'referee_reward' => 8])
            ->assertOk()
            ->assertJsonPath('referral.enabled', true);
    }
}
