<?php

namespace Tests\Feature\Plan;

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\PlanGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PlanEnforcementTest extends TestCase
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

    /** Full storefront envelope the UpdateStorefrontRequest requires. */
    private function storefrontPayload(array $overrides = []): array
    {
        return array_merge([
            'online_store_enabled' => true,
            'store_bio' => 'Welcome',
            'accent_color' => '#1890ff',
            'allow_delivery' => true,
            'allow_pickup' => true,
            'allow_dine_in' => false,
            'delivery_fee' => 5,
            'free_delivery_threshold' => 50,
            'min_order_amount' => 10,
            'whatsapp_ordering_enabled' => true,
            'show_out_of_stock_online' => true,
        ], $overrides);
    }

    public function test_free_store_cannot_claim_a_branded_slug(): void
    {
        $this->assignPlan($this->store, 'FREE');

        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/storefront', $this->storefrontPayload(['store_slug' => 'my-shop']))
            ->assertStatus(402)
            ->assertJsonPath('code', 'PLAN_UPGRADE_REQUIRED')
            ->assertJsonPath('feature', 'BRANDED_STOREFRONT');
    }

    public function test_free_store_can_still_edit_menu_appearance(): void
    {
        $this->assignPlan($this->store, 'FREE');

        // Online store off (free tier's public face is the anonymous menu) and no
        // slug -> the branded-storefront gate doesn't fire; appearance still saves.
        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/storefront', $this->storefrontPayload([
                'online_store_enabled' => false,
                'accent_color' => '#112233',
            ]))
            ->assertOk()
            ->assertJsonPath('data.storefront.accent_color', '#112233');
    }

    public function test_free_store_blocks_the_second_staff_seat(): void
    {
        $this->assignPlan($this->store, 'FREE'); // STAFF_SEATS = 1 (owner only)

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/staff/invitations', [
                'name' => 'Alex', 'email' => 'alex@example.com', 'role' => 'CASHIER',
            ])
            ->assertStatus(402)
            ->assertJsonPath('feature', 'STAFF_SEATS');
    }

    public function test_pro_store_can_invite_up_to_its_seat_cap(): void
    {
        $this->assignPlan($this->store, 'PRO'); // STAFF_SEATS = 5

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/staff/invitations', [
                'name' => 'Alex', 'email' => 'alex@example.com', 'role' => 'CASHIER',
            ])
            ->assertCreated();
    }

    public function test_product_limit_is_enforced_at_the_cap(): void
    {
        // Give the store a plan with a tiny product cap.
        $this->assignPlan($this->store, 'FREE');
        $plan = $this->store->subscription->plan;
        $plan->featureLimits()->create(['feature' => 'PRODUCTS_LIMIT', 'limit_value' => 1]);
        app(PlanGate::class)->forget($this->store);

        Product::factory()->create(['store_id' => $this->store->id]); // one existing = at the cap

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/products', ['name' => 'Second', 'price' => 5])
            ->assertStatus(402)
            ->assertJsonPath('feature', 'PRODUCTS_LIMIT');
    }
}
