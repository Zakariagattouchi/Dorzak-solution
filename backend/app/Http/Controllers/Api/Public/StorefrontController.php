<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Concerns\ResolvesPublicStore;
use App\Http\Controllers\Controller;
use App\Http\Resources\Public\PublicCategoryResource;
use App\Http\Resources\Public\PublicProductResource;
use App\Http\Resources\Public\PublicStoreResource;
use App\Support\CatalogCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StorefrontController extends Controller
{
    use ResolvesPublicStore;

    /** GET store card — cached per slug, invalidated on settings change. */
    public function show(string $slug): Response
    {
        $slug = strtolower($slug);
        $json = CatalogCache::remember($slug, 'card', function () use ($slug) {
            $store = $this->resolvePublicStore($slug);

            return (new PublicStoreResource($store))->response()->getContent();
        });

        return $this->jsonResponse($json);
    }

    /**
     * GET catalog. The common browse (no search text) is cached per slug+category;
     * free-text searches bypass the cache (unbounded key space).
     */
    public function catalog(Request $request, string $slug): Response
    {
        $slug = strtolower($slug);

        if ($request->filled('search')) {
            return $this->jsonResponse($this->buildCatalogJson($slug, $request->integer('category_id') ?: null, $request->string('search')->toString()), cache: false);
        }

        $categoryId = $request->integer('category_id') ?: null;
        $json = CatalogCache::remember($slug, 'catalog:c'.($categoryId ?? 'all'), fn () => $this->buildCatalogJson($slug, $categoryId, null));

        return $this->jsonResponse($json);
    }

    /** Build the catalog response exactly as before, returning its JSON bytes (cacheable). */
    private function buildCatalogJson(string $slug, ?int $categoryId, ?string $search): string
    {
        $store = $this->resolvePublicStore($slug);
        $sf = $store->storefrontSetting;

        $products = $store->products()
            ->where('is_active', true)
            ->where('show_in_online_store', true)
            ->with(['variants', 'category'])
            ->when(! $sf->show_out_of_stock_online, fn (Builder $q) => $q
                ->where(fn (Builder $w) => $w->where('track_stock', false)->orWhere('stock', '>', 0)))
            ->when($categoryId, fn (Builder $q) => $q->where('category_id', $categoryId))
            ->when($search, fn (Builder $q) => $q->where('name', 'like', '%'.$search.'%'))
            ->orderByDesc('is_featured')->orderBy('name')
            ->get();

        $categories = $store->categories()->orderBy('sort_order')->orderBy('name')
            ->get(['id', 'name', 'color', 'image_path']);

        return response()->json([
            'data' => [
                'categories' => PublicCategoryResource::collection($categories),
                'products' => PublicProductResource::collection($products),
            ],
        ])->getContent();
    }

    private function jsonResponse(string $json, bool $cache = true): Response
    {
        $headers = ['Content-Type' => 'application/json'];
        if ($cache && ! app()->environment('local')) {
            $headers['Cache-Control'] = 'public, max-age='.config('storefront.http_cache_max_age', 30);
        } else {
            $headers['Cache-Control'] = 'no-cache, no-store, must-revalidate';
        }

        return response($json, 200, $headers);
    }
}
