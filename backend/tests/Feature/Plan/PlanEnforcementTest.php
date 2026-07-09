<?php

namespace Tests\Feature\Plan;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\PlanGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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

    public function test_resubmitting_the_stored_slug_does_not_gate_unrelated_edits(): void
    {
        // The storefront form always sends the whole payload. A store that got a
        // slug while paid (or trialing) and later dropped to FREE must still be
        // able to save appearance edits when the unchanged slug rides along.
        $this->store->storefrontSetting->update(['slug' => 'my-shop']);
        $this->assignPlan($this->store, 'FREE');

        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/storefront', $this->storefrontPayload([
                'store_slug' => 'my-shop',
                'accent_color' => '#445566',
            ]))
            ->assertOk()
            ->assertJsonPath('data.storefront.accent_color', '#445566');

        // Changing it to a NEW slug is still a paid action.
        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/storefront', $this->storefrontPayload(['store_slug' => 'other-shop']))
            ->assertStatus(402)
            ->assertJsonPath('code', 'PLAN_UPGRADE_REQUIRED');
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
        // FREE seeds a PRODUCTS_LIMIT; override it down to 1 (updateOrCreate, since
        // the (plan_id, feature) pair is unique).
        $plan->featureLimits()->updateOrCreate(['feature' => 'PRODUCTS_LIMIT'], ['limit_value' => 1]);
        app(PlanGate::class)->forget($this->store);

        Product::factory()->create(['store_id' => $this->store->id]); // one existing = at the cap

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/products', ['name' => 'Second', 'price' => 5])
            ->assertStatus(402)
            ->assertJsonPath('feature', 'PRODUCTS_LIMIT');
    }

    public function test_pos_access_is_required_to_ring_up_an_order(): void
    {
        $this->assignPlan($this->store, 'PRO');
        // Strip POS access from this plan.
        $this->store->subscription->plan->featureLimits()->where('feature', 'POS_ACCESS')->delete();
        app(PlanGate::class)->forget($this->store);

        $product = Product::factory()->create(['store_id' => $this->store->id, 'price' => 10]);

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/orders', [
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
                'payment_method' => 'CASH',
            ])
            ->assertStatus(402)
            ->assertJsonPath('feature', 'POS_ACCESS');
    }

    public function test_pos_orders_succeed_when_the_plan_grants_pos_access(): void
    {
        $this->assignPlan($this->store, 'FREE'); // FREE includes POS_ACCESS

        $product = Product::factory()->create(['store_id' => $this->store->id, 'price' => 10]);

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/orders', [
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
                'payment_method' => 'CASH',
            ])
            ->assertCreated();
    }

    public function test_category_limit_is_enforced_at_the_cap(): void
    {
        $this->assignPlan($this->store, 'FREE');
        $this->store->subscription->plan->featureLimits()->updateOrCreate(['feature' => 'CATEGORIES_LIMIT'], ['limit_value' => 1]);
        app(PlanGate::class)->forget($this->store);

        Category::factory()->create(['store_id' => $this->store->id]); // at the cap

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/categories', ['name' => 'Second'])
            ->assertStatus(402)
            ->assertJsonPath('feature', 'CATEGORIES_LIMIT');
    }

    public function test_product_photo_limit_is_enforced(): void
    {
        Storage::fake('public');
        $this->assignPlan($this->store, 'FREE');
        $this->store->subscription->plan->featureLimits()->updateOrCreate(['feature' => 'PRODUCT_IMAGES_LIMIT'], ['limit_value' => 1]);
        app(PlanGate::class)->forget($this->store);

        $product = Product::factory()->create(['store_id' => $this->store->id, 'additional_images' => ['stores/x/a.webp']]);

        $this->actingAsMember($this->owner)
            ->postJson("/api/v1/products/{$product->id}/additional-images", [
                'file' => UploadedFile::fake()->image('extra.jpg'),
            ])
            ->assertStatus(402)
            ->assertJsonPath('feature', 'PRODUCT_IMAGES_LIMIT');
    }

    public function test_plan_feature_catalog_lists_every_capability(): void
    {
        $admin = User::factory()->create(['is_platform_admin' => true]);

        $response = $this->actingAsMember($admin)
            ->getJson('/api/v1/platform/plan-features')
            ->assertOk();

        $keys = collect($response->json('data'))->pluck('key');
        $this->assertContains('POS_ACCESS', $keys->all());
        $this->assertContains('PRODUCT_IMAGES_LIMIT', $keys->all());
        $this->assertContains('STAFF_SEATS', $keys->all());
        // Descriptors carry kind + enforced so the editor can render honestly.
        $pos = collect($response->json('data'))->firstWhere('key', 'POS_ACCESS');
        $this->assertSame('toggle', $pos['kind']);
        $this->assertTrue($pos['enforced']);
    }
}
