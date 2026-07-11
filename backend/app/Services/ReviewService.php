<?php

namespace App\Services;

use App\Enums\PlanFeature;
use App\Models\Product;
use App\Models\Review;
use App\Models\Store;

/**
 * Product reviews (premium — PlanFeature::REVIEWS). Reviews arrive pending and
 * only count toward a product's rating once a merchant approves them.
 */
class ReviewService
{
    public function __construct(private readonly PlanGate $plans) {}

    /** @param array<string, mixed> $data */
    public function submit(Store $store, Product $product, array $data): Review
    {
        $this->plans->ensure($store, PlanFeature::REVIEWS);

        return Review::create([
            'store_id' => $store->id,
            'product_id' => $product->id,
            'customer_id' => $data['customer_id'] ?? null,
            'author_name' => $data['author_name'] ?? null,
            'rating' => max(1, min(5, (int) $data['rating'])),
            'comment' => $data['comment'] ?? null,
            'approved' => false,
        ]);
    }

    public function approve(Review $review): void
    {
        $review->update(['approved' => true]);
    }

    /** @return array{average: float, count: int} */
    public function stats(Product $product): array
    {
        $q = Review::where('product_id', $product->id)->where('approved', true);

        return [
            'average' => round((float) $q->avg('rating'), 2),
            'count' => $q->count(),
        ];
    }
}
