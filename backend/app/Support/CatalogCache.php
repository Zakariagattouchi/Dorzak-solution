<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Version-keyed cache for the anonymous public storefront (store card + catalog).
 *
 * Keys embed a per-slug version counter; bumping the version instantly invalidates
 * every cached entry for that store without needing cache tags (works on any store —
 * file/database/redis). Content keys expire on their own via TTL. See docs SCALING.md.
 */
final class CatalogCache
{
    public static function ttl(): int
    {
        return (int) config('storefront.cache_ttl', 60);
    }

    public static function version(string $slug): int
    {
        return (int) Cache::get(self::versionKey($slug), 1);
    }

    /** Invalidate everything cached for a slug (structural catalog change). */
    public static function bump(?string $slug): void
    {
        if ($slug === null || $slug === '') {
            return;
        }
        Cache::forever(self::versionKey($slug), self::version($slug) + 1);
    }

    /**
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public static function remember(string $slug, string $suffix, Closure $callback)
    {
        $version = self::version($slug);

        return Cache::remember("public:{$slug}:v{$version}:{$suffix}", self::ttl(), $callback);
    }

    private static function versionKey(string $slug): string
    {
        return "public_ver:{$slug}";
    }
}
