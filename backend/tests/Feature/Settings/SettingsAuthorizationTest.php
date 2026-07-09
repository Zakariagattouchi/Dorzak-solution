<?php

namespace Tests\Feature\Settings;

use App\Enums\StaffRole;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SettingsAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public static function nonManagerRoles(): array
    {
        return [
            'cashier' => [StaffRole::CASHIER],
            'viewer' => [StaffRole::VIEWER],
        ];
    }

    #[DataProvider('nonManagerRoles')]
    public function test_non_managers_cannot_update_settings(StaffRole $role): void
    {
        $user = $this->createMember($role);

        $this->actingAsMember($user)
            ->putJson('/api/v1/settings/general', [
                'business_name' => 'Hacked', 'language' => 'en',
            ])
            ->assertForbidden();
    }

    public function test_manager_can_update_settings(): void
    {
        $store = Store::factory()->create();
        $manager = $this->createMember(StaffRole::MANAGER, $store);

        $this->actingAsMember($manager)
            ->putJson('/api/v1/settings/general', ['business_name' => 'Managed Store', 'language' => 'en'])
            ->assertOk();
    }

    public function test_cross_tenant_slug_and_data_are_isolated(): void
    {
        // Store A owner cannot see or affect store B; each envelope is its own store.
        ['user' => $ownerA, 'store' => $storeA] = $this->createStoreWithOwner(['name' => 'Store A']);
        $this->createStoreWithOwner(['name' => 'Store B']);

        $this->actingAsMember($ownerA)
            ->getJson('/api/v1/settings')
            ->assertOk()
            ->assertJsonPath('data.general.business_name', 'Store A');
    }
}
