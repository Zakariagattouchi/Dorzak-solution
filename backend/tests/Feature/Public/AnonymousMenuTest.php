<?php

namespace Tests\Feature\Public;

use App\Enums\PlanFeature;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AnonymousMenuTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // menu_token provisioning
    // -----------------------------------------------------------------------

    public function test_new_store_has_a_menu_token(): void
    {
        $store = Store::factory()->create();

        $this->assertNotNull($store->fresh()->menu_token);
        $this->assertSame(32, strlen($store->fresh()->menu_token));
    }

    public function test_menu_tokens_are_unique_across_stores(): void
    {
        $a = Store::factory()->create();
        $b = Store::factory()->create();

        $this->assertNotEquals($a->menu_token, $b->menu_token);
    }

    // -----------------------------------------------------------------------
    // GET /public/menu/{token} — store card (view-only, free stores)
    // -----------------------------------------------------------------------

    public function test_free_store_menu_returns_store_card(): void
    {
        $store = Store::factory()->create();
        $this->assignPlan($store, 'FREE');

        $this->getJson("/api/public/menu/{$store->menu_token}")
            ->assertOk()
            ->assertJsonPath('data.business_name', $store->name);
    }

    public function test_invalid_menu_token_returns_404(): void
    {
        $this->getJson('/api/public/menu/not-a-real-token-that-exists-in-db')
            ->assertNotFound();
    }

    // -----------------------------------------------------------------------
    // GET /public/menu/{token}/catalog — product list
    // -----------------------------------------------------------------------

    public function test_free_store_catalog_returns_products(): void
    {
        ['store' => $store] = $this->createStoreWithOwner();
        $this->assignPlan($store, 'FREE');
        Product::factory()->count(3)->create(['store_id' => $store->id, 'is_active' => true, 'show_in_online_store' => true]);

        $this->getJson("/api/public/menu/{$store->menu_token}/catalog")
            ->assertOk()
            ->assertJsonCount(3, 'data.products');
    }

    public function test_catalog_filters_by_search(): void
    {
        ['store' => $store] = $this->createStoreWithOwner();
        Product::factory()->create(['store_id' => $store->id, 'name' => 'Espresso', 'is_active' => true, 'show_in_online_store' => true]);
        Product::factory()->create(['store_id' => $store->id, 'name' => 'Latte', 'is_active' => true, 'show_in_online_store' => true]);

        $this->getJson("/api/public/menu/{$store->menu_token}/catalog?search=Esp")
            ->assertOk()
            ->assertJsonCount(1, 'data.products')
            ->assertJsonPath('data.products.0.name', 'Espresso');
    }

    // -----------------------------------------------------------------------
    // No ordering via menu routes — free stores cannot accept orders
    // -----------------------------------------------------------------------

    public function test_menu_route_has_no_order_endpoint(): void
    {
        $store = Store::factory()->create();

        // No order route exists under /menu/{token}/orders — should be 404 or 405.
        $status = $this->postJson("/api/public/menu/{$store->menu_token}/orders", [])->status();
        $this->assertContains($status, [404, 405]);
    }

    // -----------------------------------------------------------------------
    // PlanGate on public slug-based ordering
    // -----------------------------------------------------------------------

    public function test_paid_store_can_accept_orders_via_slug(): void
    {
        Notification::fake();
        ['store' => $store] = $this->createStoreWithOwner();
        $this->assignPlan($store, 'PRO');
        $store->storefrontSetting->update([
            'slug' => 'pro-shop', 'online_store_enabled' => true, 'allow_pickup' => true,
        ]);
        $product = Product::factory()->create([
            'store_id' => $store->id, 'name' => 'Widget', 'price' => 10,
            'is_active' => true, 'show_in_online_store' => true, 'track_stock' => false,
        ]);

        $this->postJson('/api/public/stores/pro-shop/orders', [
            'fulfillment' => 'pickup',
            'customer' => ['name' => 'Jane', 'phone' => '+97412345678'],
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertCreated();
    }

    public function test_downgraded_store_order_blocked_by_plan_gate(): void
    {
        // Store still has its slug + online_store_enabled but plan was downgraded to FREE.
        ['store' => $store] = $this->createStoreWithOwner();
        $this->assignPlan($store, 'FREE');
        $store->storefrontSetting->update([
            'slug' => 'ex-pro-shop', 'online_store_enabled' => true, 'allow_pickup' => true,
        ]);
        $product = Product::factory()->create([
            'store_id' => $store->id, 'price' => 10,
            'is_active' => true, 'show_in_online_store' => true, 'track_stock' => false,
        ]);

        $this->postJson('/api/public/stores/ex-pro-shop/orders', [
            'fulfillment' => 'pickup',
            'customer' => ['name' => 'Jane', 'phone' => '+97412345678'],
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])
            ->assertStatus(402)
            ->assertJsonPath('code', 'PLAN_UPGRADE_REQUIRED')
            ->assertJsonPath('feature', PlanFeature::ONLINE_ORDERING->value);
    }

    // -----------------------------------------------------------------------
    // Subdomain resolution — GET /public/resolve
    // -----------------------------------------------------------------------

    // -----------------------------------------------------------------------
    // Subdomain resolution — GET /public/resolve?host={hostname}
    // The SPA passes window.location.hostname when running on a subdomain.
    // -----------------------------------------------------------------------

    public function test_resolve_returns_store_card_for_valid_subdomain(): void
    {
        ['store' => $store] = $this->createStoreWithOwner();
        $this->assignPlan($store, 'PRO');
        $store->storefrontSetting->update(['slug' => 'myshop', 'online_store_enabled' => true]);

        $this->getJson('/api/public/resolve?host=myshop.dorzak.com')
            ->assertOk()
            ->assertJsonPath('data.business_name', $store->name);
    }

    public function test_resolve_returns_404_for_unknown_subdomain(): void
    {
        $this->getJson('/api/public/resolve?host=ghost.dorzak.com')
            ->assertNotFound();
    }

    public function test_resolve_returns_404_when_not_a_subdomain(): void
    {
        $this->getJson('/api/public/resolve?host=dorzak.com')
            ->assertNotFound();
    }
}
