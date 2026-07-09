<?php

namespace Tests\Feature\PublicStorefront;

use App\Models\Product;
use App\Models\Store;
use App\Support\CatalogCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontCacheTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        ['store' => $this->store] = $this->createStoreWithOwner();
        $this->store->storefrontSetting->update(['online_store_enabled' => true, 'slug' => 'cache-shop']);
    }

    private function product(string $name): Product
    {
        return Product::factory()->for($this->store)->create([
            'name' => $name, 'is_active' => true, 'show_in_online_store' => true, 'stock' => 10,
        ]);
    }

    public function test_catalog_is_served_from_cache(): void
    {
        $this->product('Alpha');

        $this->getJson('/api/public/stores/cache-shop/catalog')
            ->assertOk()->assertJsonCount(1, 'data.products');

        // Change data WITHOUT firing model events (no cache bump) — a re-query would
        // hide the product, so if it's still returned the response came from cache.
        Product::withoutEvents(fn () => Product::query()->update(['show_in_online_store' => false]));

        $this->getJson('/api/public/stores/cache-shop/catalog')
            ->assertOk()->assertJsonCount(1, 'data.products'); // still cached
    }

    public function test_structural_change_invalidates_cache(): void
    {
        $this->product('Alpha');

        $this->getJson('/api/public/stores/cache-shop/catalog')
            ->assertOk()->assertJsonCount(1, 'data.products');

        // Creating a product fires the observer -> version bump -> cache invalidated.
        $this->product('Beta');

        $this->getJson('/api/public/stores/cache-shop/catalog')
            ->assertOk()->assertJsonCount(2, 'data.products');
    }

    public function test_product_write_bumps_cache_version(): void
    {
        $p = $this->product('Alpha');
        $version = CatalogCache::version('cache-shop');

        // Any catalog write (here a price change) invalidates the store's cache.
        $p->update(['price' => 999]);
        $this->assertGreaterThan($version, CatalogCache::version('cache-shop'));
    }

    public function test_settings_change_invalidates_cache(): void
    {
        $this->product('Alpha');
        $this->getJson('/api/public/stores/cache-shop/catalog')->assertOk()->assertJsonCount(1, 'data.products');

        // Hiding the online store bumps the slug version; the card now 404s.
        $this->store->storefrontSetting->update(['online_store_enabled' => false]);

        $this->getJson('/api/public/stores/cache-shop')->assertNotFound();
    }

    public function test_public_reads_send_cache_control_header(): void
    {
        $this->product('Alpha');

        $this->getJson('/api/public/stores/cache-shop/catalog')
            ->assertOk()->assertHeader('Cache-Control', 'max-age=30, public');
        $this->getJson('/api/public/stores/cache-shop')
            ->assertOk()->assertHeader('Cache-Control', 'max-age=30, public');
    }
}
