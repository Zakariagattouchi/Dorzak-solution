<?php

namespace Tests\Feature\Loyalty;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Premium feature: the loyalty config API, gated by PlanFeature::LOYALTY. */
class LoyaltyApiTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner();
    }

    public function test_free_plan_cannot_configure_loyalty(): void
    {
        $this->assignPlan($this->store, 'FREE');

        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/loyalty', [
                'enabled' => true, 'earn_points_per_currency' => 1, 'redeem_points' => 100, 'redeem_value' => 5,
            ])
            ->assertStatus(402);
    }

    public function test_pro_plan_can_configure_and_read_loyalty(): void
    {
        $this->assignPlan($this->store, 'PRO');

        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/loyalty', [
                'enabled' => true, 'earn_points_per_currency' => 3, 'redeem_points' => 200, 'redeem_value' => 10,
            ])
            ->assertOk();

        $this->actingAsMember($this->owner)
            ->getJson('/api/v1/settings/loyalty')
            ->assertOk()
            ->assertJsonPath('loyalty.earn_points_per_currency', 3)
            ->assertJsonPath('loyalty.redeem_points', 200);
    }
}
