<?php

namespace Tests\Feature\Platform;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_platform_admin' => true]);
    }

    // -----------------------------------------------------------------------
    // Dashboard
    // -----------------------------------------------------------------------

    public function test_overview_returns_fleet_metrics(): void
    {
        $this->createStoreWithOwner();

        $this->actingAsMember($this->admin)
            ->getJson('/api/v1/platform/overview')
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'stores' => ['total', 'active', 'suspended'],
                'users_total', 'platform_admins', 'mrr_estimate', 'trials_active',
                'plan_distribution', 'signups_last_14_days',
            ]]);
    }

    // -----------------------------------------------------------------------
    // Store detail
    // -----------------------------------------------------------------------

    public function test_store_show_returns_owner_and_aggregate_metrics(): void
    {
        ['store' => $store, 'user' => $owner] = $this->createStoreWithOwner();

        $this->actingAsMember($this->admin)
            ->getJson("/api/v1/platform/stores/{$store->id}")
            ->assertOk()
            ->assertJsonPath('data.owner.email', $owner->email)
            ->assertJsonStructure(['data' => ['metrics' => ['staff', 'products', 'customers', 'orders', 'revenue']]]);
    }

    // -----------------------------------------------------------------------
    // User management
    // -----------------------------------------------------------------------

    public function test_user_index_lists_users_with_memberships(): void
    {
        ['user' => $owner, 'store' => $store] = $this->createStoreWithOwner();

        $response = $this->actingAsMember($this->admin)
            ->getJson('/api/v1/platform/users?search='.$owner->email)
            ->assertOk();

        $row = collect($response->json('data'))->firstWhere('email', $owner->email);
        $this->assertNotNull($row);
        $this->assertSame($store->name, $row['memberships'][0]['store_name']);
    }

    public function test_admin_can_grant_and_revoke_platform_admin(): void
    {
        ['user' => $owner] = $this->createStoreWithOwner();

        $this->actingAsMember($this->admin)
            ->putJson("/api/v1/platform/users/{$owner->id}/grant-admin")
            ->assertOk()
            ->assertJsonPath('data.is_platform_admin', true);

        $this->actingAsMember($this->admin)
            ->putJson("/api/v1/platform/users/{$owner->id}/revoke-admin")
            ->assertOk()
            ->assertJsonPath('data.is_platform_admin', false);
    }

    public function test_cannot_revoke_the_last_platform_admin(): void
    {
        // $this->admin is the only platform admin.
        $this->actingAsMember($this->admin)
            ->putJson("/api/v1/platform/users/{$this->admin->id}/revoke-admin")
            ->assertStatus(422);
    }

    public function test_admin_can_deactivate_a_user_across_memberships(): void
    {
        ['user' => $owner, 'store' => $store] = $this->createStoreWithOwner();

        $this->actingAsMember($this->admin)
            ->putJson("/api/v1/platform/users/{$owner->id}/active", ['is_active' => false])
            ->assertOk();

        $this->assertFalse((bool) $store->memberships()->where('user_id', $owner->id)->value('is_active'));
    }

    public function test_admin_cannot_deactivate_themselves(): void
    {
        $this->actingAsMember($this->admin)
            ->putJson("/api/v1/platform/users/{$this->admin->id}/active", ['is_active' => false])
            ->assertStatus(422);
    }

    public function test_reset_password_returns_a_temp_password_and_kills_tokens(): void
    {
        ['user' => $owner] = $this->createStoreWithOwner();
        $owner->createToken('web');
        $this->assertSame(1, $owner->tokens()->count());

        $this->actingAsMember($this->admin)
            ->postJson("/api/v1/platform/users/{$owner->id}/reset-password")
            ->assertOk()
            ->assertJsonStructure(['data' => ['temporary_password']]);

        $this->assertSame(0, $owner->fresh()->tokens()->count());
    }

    // -----------------------------------------------------------------------
    // Impersonation (god-mode)
    // -----------------------------------------------------------------------

    public function test_admin_can_impersonate_a_store_owner(): void
    {
        ['store' => $store, 'user' => $owner] = $this->createStoreWithOwner();

        // Use a real bearer token (not Sanctum::actingAs) so the follow-up call
        // below is authenticated by the returned impersonation token, not a
        // lingering acting-as user.
        $adminToken = $this->admin->createToken('web')->plainTextToken;

        $response = $this->withToken($adminToken)
            ->postJson("/api/v1/platform/stores/{$store->id}/impersonate")
            ->assertOk()
            ->assertJsonPath('data.store.id', $store->id)
            ->assertJsonPath('data.acting_as.email', $owner->email);

        $token = $response->json('data.token');

        // The impersonation token operates as the owner inside their store.
        $this->app['auth']->forgetGuards();
        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.user.email', $owner->email)
            ->assertJsonPath('data.store.id', $store->id);

        $this->assertDatabaseHas('platform_audit_logs', [
            'action' => 'store.impersonate',
            'admin_user_id' => $this->admin->id,
            'target_id' => $store->id,
        ]);
    }

    public function test_impersonating_a_store_without_an_owner_fails(): void
    {
        $store = Store::factory()->create(); // no owner membership

        $this->actingAsMember($this->admin)
            ->postJson("/api/v1/platform/stores/{$store->id}/impersonate")
            ->assertStatus(422);
    }

    // -----------------------------------------------------------------------
    // Delete
    // -----------------------------------------------------------------------

    public function test_admin_can_delete_a_store_when_name_matches(): void
    {
        ['store' => $store] = $this->createStoreWithOwner();

        $this->actingAsMember($this->admin)
            ->deleteJson("/api/v1/platform/stores/{$store->id}", ['confirm_name' => $store->name])
            ->assertNoContent();

        $this->assertDatabaseMissing('stores', ['id' => $store->id]);
        $this->assertDatabaseHas('platform_audit_logs', ['action' => 'store.delete']);
    }

    public function test_delete_rejects_a_mismatched_confirmation(): void
    {
        ['store' => $store] = $this->createStoreWithOwner();

        $this->actingAsMember($this->admin)
            ->deleteJson("/api/v1/platform/stores/{$store->id}", ['confirm_name' => 'wrong'])
            ->assertStatus(422);

        $this->assertDatabaseHas('stores', ['id' => $store->id]);
    }

    // -----------------------------------------------------------------------
    // Audit trail + authorization
    // -----------------------------------------------------------------------

    public function test_audit_log_endpoint_lists_recorded_actions(): void
    {
        ['store' => $store] = $this->createStoreWithOwner();
        $this->actingAsMember($this->admin)->putJson("/api/v1/platform/stores/{$store->id}/suspend");

        $this->actingAsMember($this->admin)
            ->getJson('/api/v1/platform/audit-logs')
            ->assertOk()
            ->assertJsonPath('data.0.action', 'store.suspend');
    }

    public function test_regular_user_cannot_reach_super_admin_endpoints(): void
    {
        ['user' => $owner] = $this->createStoreWithOwner();

        $this->actingAsMember($owner)->getJson('/api/v1/platform/overview')->assertForbidden();
        $this->actingAsMember($owner)->getJson('/api/v1/platform/users')->assertForbidden();
    }
}
