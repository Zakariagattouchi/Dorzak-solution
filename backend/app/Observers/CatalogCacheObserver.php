<?php

namespace App\Observers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\StorefrontSetting;
use App\Support\CatalogCache;
use Illuminate\Database\Eloquent\Model;

/**
 * Invalidates the public storefront cache when catalog data changes. Any product or
 * category write (including a sale's stock deduction) bumps the store's cache version.
 * Under real traffic reads vastly outnumber writes, so cache-hit rates stay very high
 * — each write just triggers one repopulating read — and stock stays fresh.
 */
class CatalogCacheObserver
{
    public function saved(Model $model): void
    {
        // The store row feeds the public card too (name, currency, and — critically —
        // the pickup coordinates that decide delivery_mode).
        if ($model instanceof Store) {
            CatalogCache::bump($model->storefrontSetting?->slug);

            return;
        }

        if ($model instanceof StorefrontSetting) {
            CatalogCache::bump($model->slug);
            // A slug change must also invalidate the previous slug's cache.
            if ($model->wasChanged('slug')) {
                CatalogCache::bump($model->getOriginal('slug'));
            }

            return;
        }

        // Product or Category write.
        CatalogCache::bump($this->slugFor($model));
    }

    public function deleted(Model $model): void
    {
        $slug = $model instanceof StorefrontSetting ? $model->slug : $this->slugFor($model);
        CatalogCache::bump($slug);
    }

    private function slugFor(Model $model): ?string
    {
        return $model instanceof Store
            ? $model->storefrontSetting?->slug
            : $model->store?->storefrontSetting?->slug;
    }
}
