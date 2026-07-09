<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\ReorderCategoriesRequest;
use App\Http\Requests\Catalog\StoreCategoryRequest;
use App\Http\Requests\Catalog\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    /** GET /categories — any member. */
    public function index(): AnonymousResourceCollection
    {
        abort_unless(request()->user()->can('categories.view'), 403);

        $categories = Category::withCount('products')
            ->orderBy('sort_order')->orderBy('name')->get();

        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());

        return (new CategoryResource($category))->response()->setStatusCode(201);
    }

    public function update(UpdateCategoryRequest $request, int $category): CategoryResource
    {
        // Resolved here (not via route-model binding) so the tenant global scope
        // is already active — a cross-store id yields 404, not a leak.
        $model = Category::findOrFail($category);
        $model->update($request->validated());

        return new CategoryResource($model);
    }

    /** DELETE /categories/{category} — products.category_id is nulled by the FK. */
    public function destroy(int $category): JsonResponse
    {
        $model = Category::findOrFail($category);
        $reassigned = $model->products()->count();
        $model->delete();

        return response()->json(['data' => ['reassigned_products' => $reassigned]]);
    }

    public function reorder(ReorderCategoriesRequest $request): JsonResponse
    {
        foreach ($request->validated('ids') as $position => $id) {
            Category::whereKey($id)->update(['sort_order' => $position]);
        }

        return response()->json(status: 204);
    }
}
