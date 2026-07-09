<?php

namespace App\Http\Concerns;

use App\Models\Store;

trait ResolvesMenuStore
{
    /** Resolve a store by its anonymous menu token (free-tier public surface). */
    protected function resolveMenuStore(string $token): Store
    {
        $store = Store::where('menu_token', $token)
            ->with('storefrontSetting')
            ->first();

        abort_if($store === null, 404, 'Menu not found.');

        return $store;
    }
}
