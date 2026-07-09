<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\UploadCategoryImageRequest;
use App\Models\Category;
use App\Support\MediaUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class CategoryImageController extends Controller
{
    public function store(UploadCategoryImageRequest $request, int $category): JsonResponse
    {
        $model = Category::findOrFail($category);
        $previous = $model->image_path;
        if ($previous && ! str_starts_with($previous, 'http')) {
            Storage::disk('public')->delete($previous);
        }

        $path = $request->file('file')->store("stores/{$model->store_id}/categories", 'public');
        $model->update(['image_path' => $path]);

        return response()->json(['data' => [
            'path' => $path,
            'url' => MediaUrl::public($path),
        ]]);
    }
}
