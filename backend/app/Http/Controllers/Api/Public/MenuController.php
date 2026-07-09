<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Concerns\ResolvesMenuStore;
use App\Http\Controllers\Controller;
use App\Http\Resources\Public\PublicCategoryResource;
use App\Http\Resources\Public\PublicProductResource;
use App\Http\Resources\Public\PublicStoreResource;
use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * View-only menu for free-tier stores. Resolved by the opaque menu_token, not a
 * branded slug — so free stores have no identity in the URL. No order or customer
 * lookup routes exist here; ordering is a paid-plan capability.
 */
class MenuController extends Controller
{
    use ResolvesMenuStore;

    public function show(string $token): JsonResponse
    {
        $store = $this->resolveMenuStore($token);

        return response()->json(['data' => (new PublicStoreResource($store))->toArray(request())]);
    }

    public function catalog(Request $request, string $token): JsonResponse
    {
        $store = $this->resolveMenuStore($token);

        return response()->json(['data' => $this->buildCatalog($store, $request)]);
    }

    private function buildCatalog(Store $store, Request $request): array
    {
        $sf = $store->storefrontSetting;
        $categoryId = $request->integer('category_id') ?: null;
        $search = $request->filled('search') ? $request->string('search')->toString() : null;

        $products = $store->products()
            ->where('is_active', true)
            ->where('show_in_online_store', true)
            ->with(['variants', 'category'])
            ->when(! $sf?->show_out_of_stock_online, fn (Builder $q) => $q
                ->where(fn (Builder $w) => $w->where('track_stock', false)->orWhere('stock', '>', 0)))
            ->when($categoryId, fn (Builder $q) => $q->where('category_id', $categoryId))
            ->when($search, fn (Builder $q) => $q->where('name', 'like', '%'.$search.'%'))
            ->orderByDesc('is_featured')->orderBy('name')
            ->get();

        $categories = $store->categories()->orderBy('sort_order')->orderBy('name')
            ->get(['id', 'name', 'color', 'image_path']);

        return [
            'categories' => PublicCategoryResource::collection($categories),
            'products' => PublicProductResource::collection($products),
        ];
    }
}
