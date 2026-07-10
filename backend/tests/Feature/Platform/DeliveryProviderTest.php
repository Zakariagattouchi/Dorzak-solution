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

    public function test_carrier_kind_and_code_round_trip(): void
    {
        $this->actingAsMember($this->admin)
            ->postJson('/api/v1/platform/delivery-providers', [
                'name' => 'Uber', 'code' => 'uber', 'kind' => 'comparator',
                'base_fee' => 10, 'per_km_fee' => 3, 'min_fee' => 12, 'max_radius_km' => 20,
            ])
            ->assertCreated()
            ->assertJsonPath('data.code', 'uber')
            ->assertJsonPath('data.kind', 'comparator');

        // Codes are the delivery network's vocabulary — they must not collide.
        $this->actingAsMember($this->admin)
            ->postJson('/api/v1/platform/delivery-providers', [
                'name' => 'Uber Clone', 'code' => 'uber',
                'base_fee' => 1, 'per_km_fee' => 1, 'min_fee' => 1, 'max_radius_km' => 5,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_only_one_network_carrier_may_exist(): void
    {
        $payload = ['kind' => 'network', 'base_fee' => 0, 'per_km_fee' => 0, 'min_fee' => 0, 'max_radius_km' => 15];

        $this->actingAsMember($this->admin)
            ->postJson('/api/v1/platform/delivery-providers', [...$payload, 'name' => 'Dorzak Delivery', 'code' => 'dorzak'])
            ->assertCreated();

        // A second one would have no quote source: the network prices a trip once.
        $this->actingAsMember($this->admin)
            ->postJson('/api/v1/platform/delivery-providers', [...$payload, 'name' => 'Dorzak Two', 'code' => 'dorzak2'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['kind']);
    }

    public function test_a_comparator_can_be_promoted_to_the_network_carrier(): void
    {
        $provider = DeliveryProvider::create(['name' => 'Dorzak', 'code' => 'dorzak', 'base_fee' => 1, 'per_km_fee' => 1, 'min_fee' => 0, 'max_radius_km' => 5]);

        $this->actingAsMember($this->admin)
            ->putJson("/api/v1/platform/delivery-providers/{$provider->id}", ['kind' => 'network'])
            ->assertOk()
            ->assertJsonPath('data.kind', 'network');

        // Re-saving it stays valid — the guard must ignore the row it is editing.
        $this->actingAsMember($this->admin)
            ->putJson("/api/v1/platform/delivery-providers/{$provider->id}", ['code' => 'dorzak', 'max_radius_km' => 25])
            ->assertOk();
    }

    public function test_regular_user_cannot_manage_providers(): void
    {
        ['user' => $owner] = $this->createStoreWithOwner();
        $provider = DeliveryProvider::create(['name' => 'X', 'base_fee' => 1, 'per_km_fee' => 1, 'min_fee' => 0, 'max_radius_km' => 5]);

        $this->actingAsMember($owner)->getJson('/api/v1/platform/delivery-providers')->assertForbidden();
        $this->actingAsMember($owner)->deleteJson("/api/v1/platform/delivery-providers/{$provider->id}")->assertForbidden();
    }
}
