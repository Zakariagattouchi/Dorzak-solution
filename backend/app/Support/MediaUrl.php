<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaUrl
{
    public static function public(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        // Externally-hosted image stored as a full URL — return as-is.
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        // Return a clean, root-relative path. The frontend proxy or web server
        // will route /storage/ requests to the correct backend host.
        return '/storage/'.ltrim($path, '/');
    }
}
