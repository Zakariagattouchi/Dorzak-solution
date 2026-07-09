<?php

namespace App\Http\Concerns;

use App\Models\Store;

trait ResolvesPublicStore
{
    /** Resolve a store by its public slug, or 404 when missing / online store disabled. */
    protected function resolvePublicStore(string $slug): Store
    {
        $store = Store::whereHas('storefrontSetting', fn ($q) => $q
            ->where('slug', strtolower($slug))
            ->where('online_store_enabled', true)
        )->with('storefrontSetting')->first();

        abort_if($store === null, 404, 'Store not found.');

        return $store;
    }
}
