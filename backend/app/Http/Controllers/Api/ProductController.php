<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreProductRequest;
use App\Http\Requests\Catalog\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    /** GET /products — searchable, filterable, paginated. */
    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('products.view'), 403);

        $query = Product::query()
            ->with(['category', 'variants'])
            ->search($request->string('search')->toString() ?: null);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->string('status')->toString() === 'active') {
            $query->where('is_active', true);
        } elseif ($request->string('status')->toString() === 'inactive') {
            $query->where('is_active', false);
        }

        match ($request->string('stock')->toString()) {
            'low' => $query->where('track_stock', true)->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0),
            'out' => $query->where('track_stock', true)->where('stock', '<=', 0),
            default => null,
        };

        $this->applySort($query, $request->string('sort')->toString());

        $perPage = min(max($request->integer('per_page', 25), 1), 200);

        return ProductResource::collection($query->paginate($perPage)->withQueryString());
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->products->create($request->validated(), $request->user());

        return (new ProductResource($product->load(['category', 'variants'])))->response()->setStatusCode(201);
    }

    public function show(int $product): ProductResource
    {
        // Resolved here so the tenant scope is active (cross-store id -> 404).
        return new ProductResource(Product::with(['category', 'variants'])->findOrFail($product));
    }

    public function update(UpdateProductRequest $request, int $product): ProductResource
    {
        $model = Product::findOrFail($product);
        $updated = $this->products->update($model, $request->validated(), $request->user());

        return new ProductResource($updated);
    }

    public function destroy(int $product): JsonResponse
    {
        Product::findOrFail($product)->delete();

        return response()->json(status: 204);
    }

    private function applySort($query, string $sort): void
    {
        [$column, $direction] = str_starts_with($sort, '-')
            ? [substr($sort, 1), 'desc']
            : [$sort, 'asc'];

        if (in_array($column, ['name', 'price', 'stock'], true)) {
            $query->orderBy($column, $direction);
        } else {
            $query->latest();
        }
    }
}
