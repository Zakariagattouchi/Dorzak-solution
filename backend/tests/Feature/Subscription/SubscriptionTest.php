<?php

namespace Tests\Feature\Subscription;

use App\Enums\StaffRole;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_read_subscription_summary(): void
    {
        ['user' => $owner, 'store' => $store] = $this->createStoreWithOwner();
        $store->subscription->update(['plan' => 'PRO', 'price' => 19.99, 'renews_at' => now()->addYear()]);

        $this->actingAsMember($owner)
            ->getJson('/api/v1/subscription')
            ->assertOk()
            ->assertJsonStructure(['data' => ['plan', 'status', 'price', 'billing_cycle', 'renews_at', 'features']])
            ->assertJsonPath('data.plan', 'PRO')
            ->assertJsonPath('data.price', '19.99');
    }

    public function test_new_store_defaults_to_free_active(): void
    {
        ['user' => $owner] = $this->createStoreWithOwner();

        $this->actingAsMember($owner)
            ->getJson('/api/v1/subscription')
            ->assertOk()
            ->assertJsonPath('data.plan', 'FREE')
            ->assertJsonPath('data.status', 'ACTIVE');
    }

    public function test_viewer_can_read_but_only_owner_hits_portal(): void
    {
        $store = Store::factory()->create();
        $viewer = $this->createMember(StaffRole::VIEWER, $store);
        $manager = $this->createMember(StaffRole::MANAGER, $store);
        $owner = $this->createMember(StaffRole::OWNER, $store);

        $this->actingAsMember($viewer)->getJson('/api/v1/subscription')->assertOk();
        $this->actingAsMember($manager)->postJson('/api/v1/subscription/portal')->assertForbidden();
        $this->actingAsMember($owner)->postJson('/api/v1/subscription/portal')->assertOk()
            ->assertJsonStructure(['data' => ['url']]);
    }

    public function test_invoice_endpoint_is_owner_only_and_stubbed(): void
    {
        ['user' => $owner] = $this->createStoreWithOwner();

        $this->actingAsMember($owner)
            ->getJson('/api/v1/subscription/invoice/latest')
            ->assertStatus(501)
            ->assertJsonPath('code', 'BILLING_NOT_CONNECTED');
    }
}
