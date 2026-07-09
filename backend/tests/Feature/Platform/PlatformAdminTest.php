<?php

namespace Tests\Feature\Platform;

use App\Models\Plan;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->regularUser] = $this->createStoreWithOwner();
        $this->admin = User::factory()->create(['is_platform_admin' => true]);
    }

    // -----------------------------------------------------------------------
    // Authorization: regular users are blocked
    // -----------------------------------------------------------------------

    public function test_regular_user_cannot_access_platform_routes(): void
    {
        $this->actingAsMember($this->regularUser)
            ->getJson('/api/v1/platform/plans')
            ->assertForbidden();
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->getJson('/api/v1/platform/plans')->assertUnauthorized();
    }

    // -----------------------------------------------------------------------
    // Plans CRUD
    // -----------------------------------------------------------------------

    public function test_platform_admin_can_list_plans(): void
    {
        $this->actingAsMember($this->admin)
            ->getJson('/api/v1/platform/plans')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'code', 'name_en', 'features']]]);
    }

    public function test_platform_admin_can_create_a_plan(): void
    {
        $response = $this->actingAsMember($this->admin)
            ->postJson('/api/v1/platform/plans', [
                'code' => 'STARTER',
                'name_en' => 'Starter',
                'name_ar' => 'مبتدئ',
                'price' => 29,
                'billing_cycle' => 'month',
                'features' => [
                    ['feature' => 'ONLINE_ORDERING'],
                    ['feature' => 'STAFF_SEATS', 'limit_value' => 2],
                ],
            ])
            ->assertCreated();

        $response->assertJsonPath('data.code', 'STARTER')
            ->assertJsonCount(2, 'data.features');
        $this->assertDatabaseHas('plans', ['code' => 'STARTER']);
    }

    public function test_platform_admin_can_update_plan_features(): void
    {
        $plan = Plan::where('code', 'PRO')->first();

        $this->actingAsMember($this->admin)
            ->putJson("/api/v1/platform/plans/{$plan->id}", [
                'price' => 119,
                'features' => [
                    ['feature' => 'ONLINE_ORDERING'],
                    ['feature' => 'BRANDED_STOREFRONT'],
                    ['feature' => 'STAFF_SEATS', 'limit_value' => 10],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.price', 119)
            ->assertJsonCount(3, 'data.features');
    }

    public function test_cannot_delete_the_default_plan(): void
    {
        $default = Plan::where('is_default', true)->first();

        $this->actingAsMember($this->admin)
            ->deleteJson("/api/v1/platform/plans/{$default->id}")
            ->assertStatus(422);
    }

    public function test_platform_admin_can_delete_non_default_plan(): void
    {
        // Create a disposable plan.
        $plan = Plan::create([
            'code' => 'TEMP', 'name_en' => 'Temp', 'name_ar' => 'مؤقت', 'price' => 0,
            'billing_cycle' => 'month', 'is_default' => false, 'is_active' => true, 'sort_order' => 99,
        ]);

        $this->actingAsMember($this->admin)
            ->deleteJson("/api/v1/platform/plans/{$plan->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('plans', ['id' => $plan->id]);
    }

    // -----------------------------------------------------------------------
    // Store list, suspend, reactivate
    // -----------------------------------------------------------------------

    public function test_platform_admin_can_list_stores(): void
    {
        $this->actingAsMember($this->admin)
            ->getJson('/api/v1/platform/stores')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_store_list_supports_status_filter(): void
    {
        ['store' => $store] = $this->createStoreWithOwner();
        $store->update(['suspended_at' => now()]);

        $response = $this->actingAsMember($this->admin)
            ->getJson('/api/v1/platform/stores?status=suspended')
            ->assertOk();

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertContains($store->id, $ids->all());
    }

    public function test_platform_admin_can_suspend_a_store(): void
    {
        ['store' => $store] = $this->createStoreWithOwner();

        $this->actingAsMember($this->admin)
            ->putJson("/api/v1/platform/stores/{$store->id}/suspend")
            ->assertOk()
            ->assertJsonPath('data.suspended_at', fn ($v) => $v !== null);

        $this->assertNotNull($store->fresh()->suspended_at);
    }

    public function test_platform_admin_can_reactivate_a_store(): void
    {
        ['store' => $store] = $this->createStoreWithOwner();
        $store->update(['suspended_at' => now()]);

        $this->actingAsMember($this->admin)
            ->putJson("/api/v1/platform/stores/{$store->id}/reactivate")
            ->assertOk()
            ->assertJsonPath('data.suspended_at', null);

        $this->assertNull($store->fresh()->suspended_at);
    }

    // -----------------------------------------------------------------------
    // Suspension enforcement: members of a suspended store are blocked
    // -----------------------------------------------------------------------

    public function test_suspended_store_members_get_403_on_api(): void
    {
        ['user' => $owner, 'store' => $store] = $this->createStoreWithOwner();
        $store->update(['suspended_at' => now()]);

        $this->actingAsMember($owner)
            ->getJson('/api/v1/settings')
            ->assertForbidden()
            ->assertJsonPath('code', 'STORE_SUSPENDED');
    }

    public function test_suspended_store_is_hidden_from_public_slug_routes(): void
    {
        ['store' => $store] = $this->createStoreWithOwner();
        $this->assignPlan($store, 'PRO');
        $store->storefrontSetting->update(['slug' => 'gone-shop', 'online_store_enabled' => true]);
        $store->update(['suspended_at' => now()]);

        $this->getJson('/api/public/stores/gone-shop')->assertNotFound();
    }

    public function test_suspended_store_menu_token_returns_404(): void
    {
        $store = Store::factory()->create();
        $store->update(['suspended_at' => now()]);

        $this->getJson("/api/public/menu/{$store->menu_token}")->assertNotFound();
    }

    // -----------------------------------------------------------------------
    // Manual plan assignment
    // -----------------------------------------------------------------------

    public function test_platform_admin_can_assign_plan_to_store(): void
    {
        ['store' => $store] = $this->createStoreWithOwner();
        $pro = Plan::where('code', 'PRO')->first();

        $this->actingAsMember($this->admin)
            ->putJson("/api/v1/platform/stores/{$store->id}/plan", ['plan_id' => $pro->id])
            ->assertOk()
            ->assertJsonPath('data.plan.code', 'PRO');

        $this->assertSame($pro->id, $store->fresh()->subscription->plan_id);
    }

    // -----------------------------------------------------------------------
    // is_platform_admin exposed in /auth/me
    // -----------------------------------------------------------------------

    public function test_me_exposes_is_platform_admin_false_for_regular_user(): void
    {
        ['user' => $owner] = $this->createStoreWithOwner();

        $this->actingAsMember($owner)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.user.is_platform_admin', false);
    }
}
