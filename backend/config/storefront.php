<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Public storefront read cache
    |--------------------------------------------------------------------------
    | Anonymous shopper reads (store card + catalog) are cached per store and
    | invalidated on catalog-structural changes (see App\Support\CatalogCache +
    | CatalogCacheObserver). Stock-only changes do NOT invalidate — the TTL bounds
    | stock staleness and the checkout re-validates availability. Tune for traffic:
    | higher TTL = fewer DB hits under load, more staleness.
    */
    'cache_ttl' => (int) env('STOREFRONT_CACHE_TTL', 60),

    // Browser/CDN cache lifetime (Cache-Control: public, max-age) for public GETs.
    'http_cache_max_age' => (int) env('STOREFRONT_HTTP_CACHE', 30),
];
