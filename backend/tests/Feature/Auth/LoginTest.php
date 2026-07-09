<?php

namespace Tests\Feature\Auth;

use App\Enums\StaffRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_receives_session_payload(): void
    {
        ['user' => $user, 'store' => $store] = $this->createStoreWithOwner(
            userAttributes: ['email' => 'owner@dorzak.com', 'password' => Hash::make('secret-password')],
        );

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'owner@dorzak.com',
            'password' => 'secret-password',
            'device_name' => 'test',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.store.id', $store->id)
            ->assertJsonPath('data.role', 'OWNER')
            ->assertJsonStructure(['data' => ['token', 'user' => ['id', 'name', 'email'], 'store', 'role', 'abilities']]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_login_rejects_wrong_password(): void
    {
        $this->createStoreWithOwner(
            userAttributes: ['email' => 'owner@dorzak.com', 'password' => Hash::make('secret-password')],
        );

        $this->postJson('/api/v1/auth/login', [
            'email' => 'owner@dorzak.com',
            'password' => 'wrong-password',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_login_rejects_unknown_email(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'nobody@dorzak.com',
            'password' => 'secret-password',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_disabled_member_gets_account_disabled(): void
    {
        $user = $this->createMember(StaffRole::CASHIER, active: false);
        $user->forceFill(['password' => Hash::make('secret-password')])->save();

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'secret-password',
        ])->assertStatus(403)->assertJsonPath('code', 'ACCOUNT_DISABLED');
    }

    public function test_bearer_token_from_login_authorizes_me(): void
    {
        $this->createStoreWithOwner(
            userAttributes: ['email' => 'owner@dorzak.com', 'password' => Hash::make('secret-password')],
        );

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'owner@dorzak.com',
            'password' => 'secret-password',
            'device_name' => 'cli',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.user.email', 'owner@dorzak.com');
    }

    public function test_login_is_throttled_after_five_attempts(): void
    {
        $this->createStoreWithOwner(
            userAttributes: ['email' => 'owner@dorzak.com', 'password' => Hash::make('secret-password')],
        );

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'owner@dorzak.com',
                'password' => 'wrong',
            ])->assertStatus(422);
        }

        $this->postJson('/api/v1/auth/login', [
            'email' => 'owner@dorzak.com',
            'password' => 'wrong',
        ])->assertStatus(429);
    }
}
