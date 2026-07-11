<?php

namespace Tests\Feature\Review;

use App\Models\Product;
use App\Models\Review;
use App\Models\Store;
use App\Services\ReviewService;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Premium feature: product reviews with moderation + rating stats. */
class ReviewServiceTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->store = Store::factory()->create();
        app(StoreContext::class)->setStore($this->store);
        $this->product = Product::factory()->for($this->store)->create();
    }

    private function review(int $rating, bool $approved): Review
    {
        return Review::create([
            'store_id' => $this->store->id, 'product_id' => $this->product->id,
            'author_name' => 'A', 'rating' => $rating, 'comment' => 'ok', 'approved' => $approved,
        ]);
    }

    public function test_stats_average_only_approved_reviews(): void
    {
        $this->review(4, true);
        $this->review(5, true);
        $this->review(1, false); // pending — excluded

        $stats = app(ReviewService::class)->stats($this->product);

        $this->assertSame(2, $stats['count']);
        $this->assertSame('4.5', number_format($stats['average'], 1));
    }

    public function test_approving_a_review_makes_it_count(): void
    {
        $review = $this->review(3, false);
        $this->assertSame(0, app(ReviewService::class)->stats($this->product)['count']);

        app(ReviewService::class)->approve($review);

        $this->assertSame(1, app(ReviewService::class)->stats($this->product)['count']);
    }
}
