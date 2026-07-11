<?php

namespace Tests\Feature\Coupon;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Premium feature: coupon CRUD API, gated by PlanFeature::COUPONS. */
class CouponApiTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner();
    }

    public function test_free_plan_cannot_create_coupons(): void
    {
        $this->assignPlan($this->store, 'FREE');

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/coupons', ['code' => 'X', 'type' => 'PERCENT', 'value' => 10])
            ->assertStatus(402);
    }

    public function test_pro_plan_creates_and_lists_coupons(): void
    {
        $this->assignPlan($this->store, 'PRO');

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/coupons', ['code' => 'welcome', 'type' => 'PERCENT', 'value' => 15])
            ->assertCreated()
            ->assertJsonPath('code', 'WELCOME');

        $this->actingAsMember($this->owner)
            ->getJson('/api/v1/coupons')
            ->assertOk()
            ->assertJsonPath('coupons.0.code', 'WELCOME')
            ->assertJsonPath('coupons.0.type', 'PERCENT');
    }
}
