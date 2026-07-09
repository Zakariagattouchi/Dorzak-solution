<?php

namespace App\Models\Concerns;

use App\Models\Scopes\StoreScope;
use App\Models\Store;
use App\Support\StoreContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tenant-scoping trait for every store-owned model (Category, Product, Customer,
 * Order, StockMovement, ...). Adds a global StoreScope that filters by the current
 * StoreContext store and auto-fills store_id on create. See docs 06 §2.
 */
trait BelongsToStore
{
    public static function bootBelongsToStore(): void
    {
        static::addGlobalScope(new StoreScope);

        static::creating(function (Model $model): void {
            if ($model->getAttribute('store_id') === null) {
                $storeId = app(StoreContext::class)->storeId();

                if ($storeId !== null) {
                    $model->setAttribute('store_id', $storeId);
                }
            }
        });
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /** Escape hatch for jobs/commands that must query across tenants deliberately. */
    public function scopeWithoutStoreScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope(StoreScope::class);
    }
}
