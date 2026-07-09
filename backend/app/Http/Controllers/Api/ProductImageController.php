<?php

namespace App\Http\Controllers\Api;

use App\Enums\PlanFeature;
use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\UploadProductImageRequest;
use App\Models\Product;
use App\Services\PlanGate;
use App\Support\MediaUrl;
use App\Support\StoreContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function __construct(
        private readonly PlanGate $plans,
        private readonly StoreContext $context,
    ) {}

    public function store(UploadProductImageRequest $request, int $product): JsonResponse
    {
        $product = Product::findOrFail($product);
        $previous = $product->image_path;
        if ($previous && ! str_starts_with($previous, 'http')) {
            Storage::disk('public')->delete($previous);
        }

        $path = $request->file('file')->store("stores/{$product->store_id}/products", 'public');
        $product->update(['image_path' => $path]);

        return response()->json([
            'data' => ['path' => $path, 'url' => MediaUrl::public($path)],
        ]);
    }

    public function storeAdditional(UploadProductImageRequest $request, int $product): JsonResponse
    {
        $product = Product::findOrFail($product);
        $images = $product->additional_images ?? [];

        // Extra photos per product are capped by the plan (null = unlimited).
        $this->plans->ensureWithinLimit($this->context->store(), PlanFeature::PRODUCT_IMAGES_LIMIT, count($images));

        $path = $request->file('file')->store("stores/{$product->store_id}/products", 'public');
        $images[] = $path;
        $product->update(['additional_images' => $images]);

        return response()->json([
            'data' => [
                'path' => $path,
                'url' => MediaUrl::public($path),
                'additional_images' => $images,
                'additional_image_urls' => array_map(fn ($p) => MediaUrl::public($p), $images),
            ],
        ]);
    }

    public function destroyAdditional(Request $request, int $product): JsonResponse
    {
        $product = Product::findOrFail($product);
        $images = $product->additional_images ?? [];
        $path = $request->input('path');

        if (($key = array_search($path, $images)) !== false) {
            unset($images[$key]);
            $images = array_values($images);
            if (! str_starts_with($path, 'http')) {
                Storage::disk('public')->delete($path);
            }
            $product->update(['additional_images' => $images]);
        }

        return response()->json([
            'data' => [
                'additional_images' => $images,
                'additional_image_urls' => array_map(fn ($p) => MediaUrl::public($p), $images),
            ],
        ]);
    }
}
