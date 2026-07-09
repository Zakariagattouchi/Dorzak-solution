<?php

namespace Tests\Feature\Catalog;

use App\Enums\StaffRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner();
    }

    public function test_lists_products_paginated(): void
    {
        Product::factory()->count(30)->for($this->store)->create();

        $this->actingAsMember($this->owner)
            ->getJson('/api/v1/products?per_page=10')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 30);
    }

    public function test_search_matches_name_and_sku(): void
    {
        Product::factory()->for($this->store)->create(['name' => 'Cotton Hoodie', 'sku' => 'HOOD-1']);
        Product::factory()->for($this->store)->create(['name' => 'Water Bottle', 'sku' => 'BTL-1']);

        $this->actingAsMember($this->owner)
            ->getJson('/api/v1/products?search=hoodie')
            ->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Cotton Hoodie');

        $this->actingAsMember($this->owner)
            ->getJson('/api/v1/products?search=BTL')
            ->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_stock_filters(): void
    {
        Product::factory()->for($this->store)->create(['stock' => 100, 'min_stock' => 5]);
        Product::factory()->for($this->store)->lowStock()->create();
        Product::factory()->for($this->store)->outOfStock()->create();

        $this->actingAsMember($this->owner)->getJson('/api/v1/products?stock=low')->assertJsonCount(1, 'data');
        $this->actingAsMember($this->owner)->getJson('/api/v1/products?stock=out')->assertJsonCount(1, 'data');
    }

    public function test_create_product_requires_name_and_price(): void
    {
        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/products', ['description' => 'x'])
            ->assertStatus(422)->assertJsonValidationErrors(['name', 'price']);
    }

    public function test_reduced_price_must_be_lower_than_price(): void
    {
        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/products', ['name' => 'X', 'price' => 10, 'reduced_price' => 12])
            ->assertStatus(422)->assertJsonValidationErrors('reduced_price');
    }

    public function test_create_product_writes_initial_stock_movement(): void
    {
        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/products', [
                'name' => 'Hoodie', 'price' => 49.99, 'cost' => 18, 'sku' => 'HOOD-001',
                'track_stock' => true, 'stock' => 45, 'min_stock' => 10,
            ])
            ->assertCreated()
            ->assertJsonPath('data.stock', 45)
            ->assertJsonPath('data.stock_status', 'IN_STOCK');

        $this->assertDatabaseHas('stock_movements', [
            'type' => 'INITIAL', 'quantity_change' => 45, 'stock_after' => 45,
        ]);
    }

    public function test_sku_auto_generated_when_blank(): void
    {
        $sku = $this->actingAsMember($this->owner)
            ->postJson('/api/v1/products', ['name' => 'NoSku', 'price' => 5])
            ->assertCreated()->json('data.sku');

        $this->assertStringStartsWith('PROD-', $sku);
    }

    public function test_sku_unique_per_store_and_reusable_after_delete(): void
    {
        $p = Product::factory()->for($this->store)->create(['sku' => 'DUP-1']);

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/products', ['name' => 'Dup', 'price' => 5, 'sku' => 'DUP-1'])
            ->assertStatus(422)->assertJsonValidationErrors('sku');

        // Soft-delete frees the SKU for reuse (partial unique index).
        $p->delete();
        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/products', ['name' => 'Reuse', 'price' => 5, 'sku' => 'DUP-1'])
            ->assertCreated();
    }

    public function test_create_with_variants_sets_parent_stock_to_sum(): void
    {
        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/products', [
                'name' => 'Hoodie', 'price' => 49.99, 'sku' => 'HOOD-V',
                'variants' => [
                    ['name' => 'S', 'price' => 49.99, 'stock' => 15],
                    ['name' => 'M', 'price' => 49.99, 'stock' => 20],
                    ['name' => 'L', 'price' => 49.99, 'stock' => 10],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.stock', 45)
            ->assertJsonCount(3, 'data.variants');
    }

    public function test_update_syncs_variants_add_update_remove(): void
    {
        $product = Product::factory()->for($this->store)->create(['price' => 20]);
        $v1 = $product->variants()->create(['name' => 'S', 'price' => 20, 'stock' => 5, 'sort_order' => 0]);
        $v2 = $product->variants()->create(['name' => 'M', 'price' => 20, 'stock' => 8, 'sort_order' => 1]);

        $this->actingAsMember($this->owner)
            ->putJson("/api/v1/products/{$product->id}", [
                'name' => $product->name, 'price' => 20,
                'variants' => [
                    ['id' => $v1->id, 'name' => 'Small', 'price' => 20, 'stock' => 7], // update
                    ['name' => 'Large', 'price' => 22, 'stock' => 4],                    // create
                    // v2 omitted -> deleted
                ],
            ])
            ->assertOk()
            ->assertJsonCount(2, 'data.variants')
            ->assertJsonPath('data.stock', 11); // 7 + 4

        $this->assertDatabaseMissing('product_variants', ['id' => $v2->id]);
        $this->assertDatabaseHas('product_variants', ['id' => $v1->id, 'name' => 'Small', 'stock' => 7]);
    }

    public function test_stock_edit_writes_adjustment_movement(): void
    {
        $product = Product::factory()->for($this->store)->create(['stock' => 10, 'track_stock' => true]);

        $this->actingAsMember($this->owner)
            ->putJson("/api/v1/products/{$product->id}", [
                'name' => $product->name, 'price' => $product->price, 'track_stock' => true, 'stock' => 25,
            ])
            ->assertOk()->assertJsonPath('data.stock', 25);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id, 'type' => 'ADJUSTMENT', 'quantity_change' => 15, 'stock_after' => 25,
        ]);
    }

    public function test_soft_delete_hides_from_list(): void
    {
        $product = Product::factory()->for($this->store)->create();

        $this->actingAsMember($this->owner)->deleteJson("/api/v1/products/{$product->id}")->assertNoContent();
        $this->actingAsMember($this->owner)->getJson('/api/v1/products')->assertJsonCount(0, 'data');
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_category_must_belong_to_store(): void
    {
        $foreignCategory = Category::factory()->create();

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/products', ['name' => 'X', 'price' => 5, 'category_id' => $foreignCategory->id])
            ->assertStatus(422)->assertJsonValidationErrors('category_id');
    }

    public function test_viewer_cannot_create_product(): void
    {
        $viewer = $this->createMember(StaffRole::VIEWER, $this->store);

        $this->actingAsMember($viewer)
            ->postJson('/api/v1/products', ['name' => 'X', 'price' => 5])
            ->assertForbidden();
    }

    public function test_cross_tenant_product_is_not_found(): void
    {
        $foreign = Product::factory()->create();

        $this->actingAsMember($this->owner)
            ->getJson("/api/v1/products/{$foreign->id}")
            ->assertNotFound();
    }
}
