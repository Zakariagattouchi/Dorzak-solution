<?php

namespace Tests\Feature\Segment;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Premium feature: segment API gated by PlanFeature::SEGMENTS. */
class SegmentApiTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner();
    }

    public function test_free_plan_cannot_create_segments(): void
    {
        $this->assignPlan($this->store, 'FREE');

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/segments', ['name' => 'VIP', 'rules' => ['min_spent' => 100]])
            ->assertStatus(402);
    }

    public function test_enterprise_plan_creates_and_lists_segments(): void
    {
        $this->assignPlan($this->store, 'ENTERPRISE');

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/segments', ['name' => 'VIP', 'rules' => ['min_orders' => 3, 'min_spent' => 100]])
            ->assertCreated();

        $this->actingAsMember($this->owner)
            ->getJson('/api/v1/segments')
            ->assertOk()
            ->assertJsonPath('segments.0.name', 'VIP');
    }
}
