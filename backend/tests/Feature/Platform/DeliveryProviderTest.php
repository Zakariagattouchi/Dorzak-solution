<?php

namespace Tests\Feature\Platform;

use App\Models\DeliveryProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryProviderTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_platform_admin' => true]);
    }

    public function test_admin_can_create_list_update_and_delete_providers(): void
    {
        $created = $this->actingAsMember($this->admin)
            ->postJson('/api/v1/platform/delivery-providers', [
                'name' => 'Dorzak Delivery',
                'base_fee' => 5,
                'per_km_fee' => 2,
                'min_fee' => 8,
                'max_radius_km' => 15,
                'is_plan_gated' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Dorzak Delivery')
            ->assertJsonPath('data.is_plan_gated', true);

        $id = $created->json('data.id');

        $this->actingAsMember($this->admin)
            ->getJson('/api/v1/platform/delivery-providers')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Dorzak Delivery');

        $this->actingAsMember($this->admin)
            ->putJson("/api/v1/platform/delivery-providers/{$id}", ['per_km_fee' => 2.5])
            ->assertOk()
            ->assertJsonPath('data.per_km_fee', 2.5);

        $this->actingAsMember($this->admin)
            ->deleteJson("/api/v1/platform/delivery-providers/{$id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('delivery_providers', ['id' => $id]);

        foreach (['delivery_provider.created', 'delivery_provider.updated', 'delivery_provider.deleted'] as $action) {
            $this->assertDatabaseHas('platform_audit_logs', ['action' => $action]);
        }
    }

    public function test_validation_rejects_nonsense(): void
    {
        $this->actingAsMember($this->admin)
            ->postJson('/api/v1/platform/delivery-providers', [
                'name' => '',
                'base_fee' => -1,
                'per_km_fee' => 2,
                'min_fee' => 0,
                'max_radius_km' => 0,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'base_fee', 'max_radius_km']);
    }

    public function test_regular_user_cannot_manage_providers(): void
    {
        ['user' => $owner] = $this->createStoreWithOwner();
        $provider = DeliveryProvider::create(['name' => 'X', 'base_fee' => 1, 'per_km_fee' => 1, 'min_fee' => 0, 'max_radius_km' => 5]);

        $this->actingAsMember($owner)->getJson('/api/v1/platform/delivery-providers')->assertForbidden();
        $this->actingAsMember($owner)->deleteJson("/api/v1/platform/delivery-providers/{$provider->id}")->assertForbidden();
    }
}
