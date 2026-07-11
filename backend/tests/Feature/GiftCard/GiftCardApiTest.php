<?php

namespace Tests\Feature\GiftCard;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Premium feature: gift-card API gated by PlanFeature::GIFT_CARDS. */
class GiftCardApiTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner();
    }

    public function test_free_plan_cannot_issue_gift_cards(): void
    {
        $this->assignPlan($this->store, 'FREE');

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/gift-cards', ['amount' => 50])
            ->assertStatus(402);
    }

    public function test_enterprise_plan_issues_and_lists_gift_cards(): void
    {
        $this->assignPlan($this->store, 'ENTERPRISE');

        $code = $this->actingAsMember($this->owner)
            ->postJson('/api/v1/gift-cards', ['amount' => 50])
            ->assertCreated()
            ->json('code');

        $this->assertStringStartsWith('GC-', $code);

        $this->actingAsMember($this->owner)
            ->getJson('/api/v1/gift-cards')
            ->assertOk()
            ->assertJsonPath('gift_cards.0.code', $code)
            ->assertJsonPath('gift_cards.0.status', 'active');
    }
}
