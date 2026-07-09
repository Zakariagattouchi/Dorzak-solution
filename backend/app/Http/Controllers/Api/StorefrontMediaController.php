<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UploadStorefrontMediaRequest;
use App\Support\MediaUrl;
use App\Support\StoreContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class StorefrontMediaController extends Controller
{
    public function __construct(private readonly StoreContext $context) {}

    public function banner(UploadStorefrontMediaRequest $request): JsonResponse
    {
        return $this->store($request, 'banner_path');
    }

    public function logo(UploadStorefrontMediaRequest $request): JsonResponse
    {
        return $this->store($request, 'logo_path');
    }

    private function store(UploadStorefrontMediaRequest $request, string $column): JsonResponse
    {
        $store = $this->context->store();
        $setting = $store->storefrontSetting()->firstOrCreate([]);

        // Remove the previously stored file (skip absolute URLs set via the settings form).
        $previous = $setting->getAttribute($column);
        if ($previous && ! str_starts_with($previous, 'http')) {
            Storage::disk('public')->delete($previous);
        }

        $path = $request->file('file')->store("stores/{$store->id}", 'public');
        $setting->update([$column => $path]);

        return response()->json([
            'data' => [
                'path' => $path,
                'url' => MediaUrl::public($path),
            ],
        ]);
    }
}
