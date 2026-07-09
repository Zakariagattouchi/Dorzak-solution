<?php

namespace Tests\Feature\Staff;

use App\Enums\StaffRole;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner();
    }

    public function test_index_lists_members_and_pending_invitations(): void
    {
        $this->createMember(StaffRole::CASHIER, $this->store);
        $this->store->staffInvitations()->create([
            'invited_by' => $this->owner->id, 'name' => 'Pending Pat', 'email' => 'pat@e.com',
            'role' => StaffRole::VIEWER, 'token' => 't1', 'expires_at' => now()->addDays(7),
        ]);

        $data = $this->actingAsMember($this->owner)->getJson('/api/v1/staff')->assertOk()->json('data');

        $this->assertCount(3, $data); // owner + cashier + pending
        $this->assertTrue(collect($data)->firstWhere('email', 'pat@e.com')['invitation_pending']);
    }

    public function test_manager_can_change_cashier_role_and_deactivate(): void
    {
        $manager = $this->createMember(StaffRole::MANAGER, $this->store);
        $cashier = $this->createMember(StaffRole::CASHIER, $this->store);

        $this->actingAsMember($manager)
            ->patchJson("/api/v1/staff/{$cashier->id}", ['role' => 'VIEWER', 'is_active' => false])
            ->assertOk()
            ->assertJsonPath('data.role', 'VIEWER')
            ->assertJsonPath('data.is_active', false);
    }

    public function test_manager_cannot_modify_owner(): void
    {
        $manager = $this->createMember(StaffRole::MANAGER, $this->store);

        $this->actingAsMember($manager)
            ->patchJson("/api/v1/staff/{$this->owner->id}", ['is_active' => false])
            ->assertForbidden();
    }

    public function test_cannot_deactivate_last_owner(): void
    {
        $this->actingAsMember($this->owner)
            ->patchJson("/api/v1/staff/{$this->owner->id}", ['is_active' => false])
            ->assertStatus(409)
            ->assertJsonPath('code', 'LAST_OWNER');
    }

    public function test_cannot_demote_last_owner(): void
    {
        $this->actingAsMember($this->owner)
            ->patchJson("/api/v1/staff/{$this->owner->id}", ['role' => 'MANAGER'])
            ->assertStatus(409)
            ->assertJsonPath('code', 'LAST_OWNER');
    }

    public function test_second_owner_allows_demoting_the_first(): void
    {
        $secondOwner = $this->createMember(StaffRole::OWNER, $this->store);

        $this->actingAsMember($this->owner)
            ->patchJson("/api/v1/staff/{$secondOwner->id}", ['role' => 'MANAGER'])
            ->assertOk()
            ->assertJsonPath('data.role', 'MANAGER');
    }

    public function test_deactivation_revokes_tokens(): void
    {
        $cashier = $this->createMember(StaffRole::CASHIER, $this->store);
        $cashier->createToken('cli');
        $this->assertSame(1, $cashier->tokens()->count());

        $this->actingAsMember($this->owner)
            ->patchJson("/api/v1/staff/{$cashier->id}", ['is_active' => false])
            ->assertOk();

        $this->assertSame(0, $cashier->fresh()->tokens()->count());
    }

    public function test_remove_member_detaches_but_keeps_user(): void
    {
        $cashier = $this->createMember(StaffRole::CASHIER, $this->store);

        $this->actingAsMember($this->owner)
            ->deleteJson("/api/v1/staff/{$cashier->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('store_user', ['store_id' => $this->store->id, 'user_id' => $cashier->id]);
        $this->assertDatabaseHas('users', ['id' => $cashier->id]);
    }

    public function test_cross_tenant_member_is_not_found(): void
    {
        $otherStore = Store::factory()->create();
        $otherMember = $this->createMember(StaffRole::CASHIER, $otherStore);

        $this->actingAsMember($this->owner)
            ->patchJson("/api/v1/staff/{$otherMember->id}", ['is_active' => false])
            ->assertNotFound();
    }
}
