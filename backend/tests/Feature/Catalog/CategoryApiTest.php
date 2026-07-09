<?php

namespace Tests\Feature\Catalog;

use App\Enums\StaffRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner();
    }

    public function test_lists_categories_with_product_counts(): void
    {
        $cat = Category::factory()->for($this->store)->create(['name' => 'Apparel']);
        Product::factory()->count(2)->for($this->store)->for($cat)->create();

        $this->actingAsMember($this->owner)
            ->getJson('/api/v1/categories')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Apparel')
            ->assertJsonPath('data.0.products_count', 2);
    }

    public function test_create_category(): void
    {
        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/categories', ['name' => 'Coffee', 'color' => '#f59e0b'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Coffee');

        $this->assertDatabaseHas('categories', ['store_id' => $this->store->id, 'name' => 'Coffee']);
    }

    public function test_name_is_unique_per_store_but_not_globally(): void
    {
        Category::factory()->for($this->store)->create(['name' => 'Coffee']);

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/categories', ['name' => 'Coffee'])
            ->assertStatus(422)->assertJsonValidationErrors('name');

        // A different store may reuse the name.
        $otherStore = Store::factory()->create();
        $otherOwner = $this->createMember(StaffRole::OWNER, $otherStore);
        $this->actingAsMember($otherOwner)
            ->postJson('/api/v1/categories', ['name' => 'Coffee'])
            ->assertCreated();
    }

    public function test_delete_category_nulls_products(): void
    {
        $cat = Category::factory()->for($this->store)->create();
        $products = Product::factory()->count(3)->for($this->store)->for($cat)->create();

        $this->actingAsMember($this->owner)
            ->deleteJson("/api/v1/categories/{$cat->id}")
            ->assertOk()
            ->assertJsonPath('data.reassigned_products', 3);

        foreach ($products as $p) {
            $this->assertNull($p->fresh()->category_id);
        }
    }

    public function test_reorder_persists(): void
    {
        $a = Category::factory()->for($this->store)->create(['name' => 'A']);
        $b = Category::factory()->for($this->store)->create(['name' => 'B']);

        $this->actingAsMember($this->owner)
            ->patchJson('/api/v1/categories/reorder', ['ids' => [$b->id, $a->id]])
            ->assertNoContent();

        $this->assertSame(0, $b->fresh()->sort_order);
        $this->assertSame(1, $a->fresh()->sort_order);
    }

    public function test_cashier_can_manage_but_viewer_cannot(): void
    {
        $cashier = $this->createMember(StaffRole::CASHIER, $this->store);
        $viewer = $this->createMember(StaffRole::VIEWER, $this->store);

        $this->actingAsMember($cashier)->postJson('/api/v1/categories', ['name' => 'C1'])->assertCreated();
        $this->actingAsMember($viewer)->postJson('/api/v1/categories', ['name' => 'C2'])->assertForbidden();
        $this->actingAsMember($viewer)->getJson('/api/v1/categories')->assertOk();
    }

    public function test_cross_tenant_category_is_not_found(): void
    {
        $otherCat = Category::factory()->create();

        $this->actingAsMember($this->owner)
            ->putJson("/api/v1/categories/{$otherCat->id}", ['name' => 'X'])
            ->assertNotFound();
    }
}
