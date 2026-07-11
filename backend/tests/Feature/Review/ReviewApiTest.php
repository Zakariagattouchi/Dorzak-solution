<?php

namespace Tests\Feature\Review;

use App\Models\Product;
use App\Models\Review;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Premium feature: review API gated by PlanFeature::REVIEWS. */
class ReviewApiTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner();
    }

    public function test_free_plan_cannot_collect_reviews(): void
    {
        $this->assignPlan($this->store, 'FREE');
        $product = Product::factory()->for($this->store)->create();

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/reviews', ['product_id' => $product->id, 'rating' => 5])
            ->assertStatus(402);
    }

    public function test_enterprise_plan_collects_and_approves_a_review(): void
    {
        $this->assignPlan($this->store, 'ENTERPRISE');
        $product = Product::factory()->for($this->store)->create();

        $id = $this->actingAsMember($this->owner)
            ->postJson('/api/v1/reviews', ['product_id' => $product->id, 'rating' => 5, 'author_name' => 'Sam', 'comment' => 'Great'])
            ->assertCreated()
            ->json('id');

        $this->actingAsMember($this->owner)
            ->postJson("/api/v1/reviews/{$id}/approve")
            ->assertOk();

        $this->assertTrue(Review::find($id)->approved);
    }
}
