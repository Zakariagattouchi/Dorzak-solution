<?php

namespace App\Http\Controllers\Api;

use App\Enums\PlanFeature;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Services\PlanGate;
use App\Services\ReviewService;
use App\Support\StoreContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Product reviews + moderation (premium — PlanFeature::REVIEWS). */
class ReviewController extends Controller
{
    public function __construct(
        private readonly PlanGate $plans,
        private readonly StoreContext $context,
        private readonly ReviewService $reviews,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $q = Review::orderByDesc('id');
        if ($request->boolean('pending')) {
            $q->where('approved', false);
        }

        $rows = $q->with('product:id,name')->limit(200)->get()->map(fn (Review $r) => [
            'id' => $r->id, 'product_id' => $r->product_id, 'product_name' => $r->product?->name,
            'author_name' => $r->author_name, 'rating' => $r->rating, 'comment' => $r->comment,
            'approved' => $r->approved, 'created_at' => $r->created_at?->toIso8601String(),
        ]);

        return response()->json(['reviews' => $rows]);
    }

    public function store(Request $request): JsonResponse
    {
        $store = $this->context->store();
        $this->plans->ensure($store, PlanFeature::REVIEWS);

        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'author_name' => ['nullable', 'string', 'max:120'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'customer_id' => ['nullable', 'integer'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $review = $this->reviews->submit($store, $product, $data);

        return response()->json(['id' => $review->id], 201);
    }

    public function approve(Request $request, Review $review): JsonResponse
    {
        abort_unless($review->store_id === $this->context->store()->id, 404);
        abort_unless($request->user()->can('products.manage'), 403);

        $this->reviews->approve($review);

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request, Review $review): JsonResponse
    {
        abort_unless($review->store_id === $this->context->store()->id, 404);
        abort_unless($request->user()->can('products.manage'), 403);

        $review->delete();

        return response()->json(['ok' => true]);
    }
}
