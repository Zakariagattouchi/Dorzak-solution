<?php

namespace Tests\Feature\Staff;

use App\Enums\StaffRole;
use App\Mail\StaffInvitationMail;
use App\Models\StaffInvitation;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class StaffInvitationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner();
    }

    public function test_owner_can_invite_staff_and_mail_is_queued(): void
    {
        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/staff/invitations', [
                'name' => 'Alex Cashier', 'email' => 'alex@example.com', 'role' => 'CASHIER',
            ])
            ->assertCreated()
            ->assertJsonPath('data.invitation_pending', true)
            ->assertJsonPath('data.role', 'CASHIER');

        $this->assertDatabaseHas('staff_invitations', ['email' => 'alex@example.com', 'role' => 'CASHIER']);
        Mail::assertQueued(StaffInvitationMail::class);
    }

    public function test_cannot_invite_owner_role(): void
    {
        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/staff/invitations', [
                'name' => 'X', 'email' => 'x@example.com', 'role' => 'OWNER',
            ])
            ->assertStatus(422)->assertJsonValidationErrors('role');
    }

    public function test_cannot_invite_existing_member(): void
    {
        $existing = $this->createMember(StaffRole::CASHIER, $this->store);

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/staff/invitations', [
                'name' => 'Dup', 'email' => $existing->email, 'role' => 'CASHIER',
            ])
            ->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_cannot_create_duplicate_pending_invite(): void
    {
        $payload = ['name' => 'Alex', 'email' => 'alex@example.com', 'role' => 'CASHIER'];
        $this->actingAsMember($this->owner)->postJson('/api/v1/staff/invitations', $payload)->assertCreated();
        $this->actingAsMember($this->owner)->postJson('/api/v1/staff/invitations', $payload)
            ->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_cashier_cannot_invite(): void
    {
        $cashier = $this->createMember(StaffRole::CASHIER, $this->store);

        $this->actingAsMember($cashier)
            ->postJson('/api/v1/staff/invitations', ['name' => 'X', 'email' => 'x@e.com', 'role' => 'CASHIER'])
            ->assertForbidden();
    }

    public function test_accept_invitation_creates_user_and_membership(): void
    {
        $invite = StaffInvitation::create([
            'store_id' => $this->store->id, 'invited_by' => $this->owner->id,
            'name' => 'Alex', 'email' => 'alex@example.com', 'role' => StaffRole::CASHIER,
            'token' => 'valid-token', 'expires_at' => now()->addDays(7),
        ]);

        $this->postJson('/api/v1/staff/invitations/valid-token/accept', [
            'password' => 'secret-password', 'password_confirmation' => 'secret-password', 'device_name' => 'cli',
        ])
            ->assertCreated()
            ->assertJsonPath('data.role', 'CASHIER')
            ->assertJsonPath('data.user.email', 'alex@example.com');

        $user = User::where('email', 'alex@example.com')->firstOrFail();
        $this->assertDatabaseHas('store_user', [
            'store_id' => $this->store->id, 'user_id' => $user->id, 'role' => 'CASHIER', 'is_active' => true,
        ]);
        $this->assertNotNull($invite->fresh()->accepted_at);
    }

    public function test_expired_token_returns_410(): void
    {
        StaffInvitation::create([
            'store_id' => $this->store->id, 'invited_by' => $this->owner->id,
            'name' => 'Alex', 'email' => 'alex@example.com', 'role' => StaffRole::CASHIER,
            'token' => 'old-token', 'expires_at' => now()->subDay(),
        ]);

        $this->postJson('/api/v1/staff/invitations/old-token/accept', [
            'password' => 'secret-password', 'password_confirmation' => 'secret-password',
        ])->assertStatus(410);
    }

    public function test_already_accepted_token_returns_409(): void
    {
        StaffInvitation::create([
            'store_id' => $this->store->id, 'invited_by' => $this->owner->id,
            'name' => 'Alex', 'email' => 'alex@example.com', 'role' => StaffRole::CASHIER,
            'token' => 'used-token', 'expires_at' => now()->addDay(), 'accepted_at' => now(),
        ]);

        $this->postJson('/api/v1/staff/invitations/used-token/accept', [
            'password' => 'secret-password', 'password_confirmation' => 'secret-password',
        ])->assertStatus(409);
    }

    public function test_unknown_token_returns_404(): void
    {
        $this->postJson('/api/v1/staff/invitations/nope/accept', [
            'password' => 'secret-password', 'password_confirmation' => 'secret-password',
        ])->assertNotFound();
    }
}
