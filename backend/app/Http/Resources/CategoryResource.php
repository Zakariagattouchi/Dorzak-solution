<?php

namespace App\Http\Resources;

use App\Models\Category;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Category
 */
class CategoryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'description' => $this->description,
            'image_path' => $this->image_path,
            'image_url' => $this->mediaUrl($this->image_path),
            'sort_order' => $this->sort_order,
            'products_count' => $this->whenCounted('products'),
        ];
    }

    private function mediaUrl(?string $path): ?string
    {
        return MediaUrl::public($path);
    }
}
