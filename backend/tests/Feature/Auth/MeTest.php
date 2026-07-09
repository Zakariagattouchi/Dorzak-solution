<?php

namespace Tests\Feature\Auth;

use App\Enums\StaffRole;
use App\Models\Store;
use App\Support\RoleMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeTest extends TestCase
{
    use RefreshDatabase;

    public function test_me_returns_user_store_role_and_abilities(): void
    {
        ['user' => $user, 'store' => $store] = $this->createStoreWithOwner();

        $this->actingAsMember($user)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.store.id', $store->id)
            ->assertJsonPath('data.role', 'OWNER')
            ->assertJsonPath('data.token', null)
            ->assertExactJson([
                'data' => [
                    'token' => null,
                    'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'is_platform_admin' => false],
                    'store' => [
                        'id' => $store->id,
                        'name' => $store->name,
                        'currency' => $store->currency,
                        'symbol_placement' => $store->symbol_placement,
                        'language' => $store->language,
                        'country' => $store->country,
                    ],
                    'role' => 'OWNER',
                    'abilities' => RoleMatrix::abilitiesFor(StaffRole::OWNER),
                ],
            ]);
    }

    public function test_me_reflects_the_members_role(): void
    {
        $store = Store::factory()->create();
        $cashier = $this->createMember(StaffRole::CASHIER, $store);

        $this->actingAsMember($cashier)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.role', 'CASHIER')
            ->assertJsonPath('data.abilities', RoleMatrix::abilitiesFor(StaffRole::CASHIER));
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/v1/auth/me')->assertUnauthorized();
    }

    public function test_disabled_member_is_forbidden_on_authed_routes(): void
    {
        $user = $this->createMember(StaffRole::MANAGER, active: false);

        $this->actingAsMember($user)
            ->getJson('/api/v1/auth/me')
            ->assertStatus(403)
            ->assertJsonPath('code', 'ACCOUNT_DISABLED');
    }

    public function test_logout_revokes_and_returns_204(): void
    {
        ['user' => $user] = $this->createStoreWithOwner();

        $this->actingAsMember($user)
            ->postJson('/api/v1/auth/logout')
            ->assertNoContent();
    }
}
