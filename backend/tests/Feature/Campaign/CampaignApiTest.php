<?php

namespace Tests\Feature\Campaign;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Premium feature: campaign API gated by PlanFeature::CAMPAIGNS. */
class CampaignApiTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner();
    }

    public function test_free_plan_cannot_create_campaigns(): void
    {
        $this->assignPlan($this->store, 'FREE');

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/campaigns', ['subject' => 'Hi', 'body' => 'Sale', 'audience' => ['type' => 'all']])
            ->assertStatus(402);
    }

    public function test_enterprise_plan_schedules_a_campaign(): void
    {
        $this->assignPlan($this->store, 'ENTERPRISE');

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/campaigns', [
                'subject' => 'Weekend sale', 'body' => '20% off', 'audience' => ['type' => 'all'],
                'scheduled_at' => now()->addHour()->toIso8601String(),
            ])
            ->assertCreated()
            ->assertJsonPath('status', 'scheduled');
    }
}
