<?php

namespace App\Models\Scopes;

use App\Support\StoreContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global tenant scope applied by the BelongsToStore trait. Named (not anonymous)
 * so it can be lifted with withoutGlobalScope(StoreScope::class) in jobs/services
 * that must operate across tenants deliberately.
 */
class StoreScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $storeId = app(StoreContext::class)->storeId();

        if ($storeId !== null) {
            $builder->where($model->getTable().'.store_id', $storeId);
        }
    }
}
