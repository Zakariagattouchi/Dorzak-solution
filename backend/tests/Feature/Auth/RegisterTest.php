<?php

namespace Tests\Feature\Auth;

use App\Enums\StaffRole;
use App\Models\Store;
use App\Models\StoreUser;
use App\Models\User;
use App\Support\RoleMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    private array $payload = [
        'name' => 'Barsha Admin',
        'email' => 'owner@dorzak.com',
        'password' => 'secret-password',
        'password_confirmation' => 'secret-password',
        'business_name' => 'Dorzak Merchant',
        'device_name' => 'test',
    ];

    public function test_register_creates_user_store_owner_pivot_and_returns_session(): void
    {
        $response = $this->postJson('/api/v1/auth/register', $this->payload);

        $response->assertCreated()
            ->assertJsonPath('data.user.email', 'owner@dorzak.com')
            ->assertJsonPath('data.store.name', 'Dorzak Merchant')
            ->assertJsonPath('data.role', 'OWNER');

        $this->assertNotEmpty($response->json('data.token'));

        $this->assertDatabaseHas('users', ['email' => 'owner@dorzak.com']);
        $this->assertDatabaseHas('stores', ['name' => 'Dorzak Merchant', 'owner_name' => 'Barsha Admin']);

        $user = User::where('email', 'owner@dorzak.com')->firstOrFail();
        $store = Store::where('name', 'Dorzak Merchant')->firstOrFail();

        $this->assertDatabaseHas('store_user', [
            'user_id' => $user->id,
            'store_id' => $store->id,
            'role' => StaffRole::OWNER->value,
            'is_active' => true,
        ]);
    }

    public function test_register_response_includes_store_defaults(): void
    {
        // The SPA's money formatter needs currency/language present immediately.
        $this->postJson('/api/v1/auth/register', $this->payload)
            ->assertCreated()
            ->assertJsonPath('data.store.currency', 'USD')
            ->assertJsonPath('data.store.symbol_placement', 'BEFORE')
            ->assertJsonPath('data.store.language', 'en')
            ->assertJsonPath('data.store.country', 'United States');
    }

    public function test_register_seeds_the_three_settings_rows(): void
    {
        $this->postJson('/api/v1/auth/register', $this->payload)->assertCreated();

        $store = Store::where('name', 'Dorzak Merchant')->firstOrFail();
        $this->assertDatabaseHas('storefront_settings', ['store_id' => $store->id]);
        $this->assertDatabaseHas('receipt_settings', ['store_id' => $store->id]);
        $this->assertDatabaseHas('integration_settings', ['store_id' => $store->id]);
    }

    public function test_register_returns_full_owner_ability_set(): void
    {
        $abilities = $this->postJson('/api/v1/auth/register', $this->payload)
            ->json('data.abilities');

        $this->assertSame(RoleMatrix::abilitiesFor(StaffRole::OWNER), $abilities);
    }

    public function test_register_hashes_the_password(): void
    {
        $this->postJson('/api/v1/auth/register', $this->payload)->assertCreated();

        $user = User::where('email', 'owner@dorzak.com')->firstOrFail();
        $this->assertNotSame('secret-password', $user->password);
        $this->assertTrue(password_get_info($user->password)['algo'] !== null);
    }

    public function test_register_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'owner@dorzak.com']);

        $this->postJson('/api/v1/auth/register', $this->payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_register_requires_matching_password_confirmation(): void
    {
        $payload = array_merge($this->payload, ['password_confirmation' => 'different']);

        $this->postJson('/api/v1/auth/register', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    public function test_register_requires_business_name(): void
    {
        $payload = $this->payload;
        unset($payload['business_name']);

        $this->postJson('/api/v1/auth/register', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('business_name');
    }

    public function test_register_is_atomic_on_failure(): void
    {
        // A duplicate email aborts before any store row is written.
        User::factory()->create(['email' => 'owner@dorzak.com']);

        $this->postJson('/api/v1/auth/register', $this->payload)->assertStatus(422);

        $this->assertSame(0, Store::where('name', 'Dorzak Merchant')->count());
        $this->assertSame(0, StoreUser::query()->count());
    }
}
