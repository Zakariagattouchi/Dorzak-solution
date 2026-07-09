<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PlatformAdminSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_less_admin_can_log_in_and_gets_a_platform_session(): void
    {
        User::factory()->create([
            'email' => 'admin@dorzak.com',
            'password' => Hash::make('super-secret-1'),
            'is_platform_admin' => true,
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@dorzak.com',
            'password' => 'super-secret-1',
            'device_name' => 'web',
        ])
            ->assertOk()
            ->assertJsonPath('data.store', null)
            ->assertJsonPath('data.role', 'PLATFORM_ADMIN')
            ->assertJsonPath('data.user.is_platform_admin', true)
            ->assertJson(fn ($json) => $json->has('data.token')->etc());
    }

    public function test_platform_admin_me_returns_platform_session_without_a_store(): void
    {
        $admin = User::factory()->create(['is_platform_admin' => true]);

        $this->actingAsMember($admin)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.store', null)
            ->assertJsonPath('data.role', 'PLATFORM_ADMIN');
    }

    public function test_store_less_non_admin_is_still_rejected(): void
    {
        User::factory()->create([
            'email' => 'nobody@nowhere.com',
            'password' => Hash::make('super-secret-1'),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'nobody@nowhere.com',
            'password' => 'super-secret-1',
        ])->assertForbidden();
    }

    public function test_platform_admin_cannot_reach_merchant_store_routes(): void
    {
        $admin = User::factory()->create(['is_platform_admin' => true]);

        // No membership -> the store guard blocks merchant endpoints.
        $this->actingAsMember($admin)->getJson('/api/v1/settings')->assertForbidden();
    }
}
