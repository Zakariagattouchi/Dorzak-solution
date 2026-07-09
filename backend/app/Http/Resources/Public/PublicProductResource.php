<?php

namespace App\Http\Resources\Public;

use App\Models\Product;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
class PublicProductResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'description' => $this->description,
            'description_ar' => $this->description_ar,
            'price' => $this->price,
            'reduced_price' => $this->reduced_price,
            'effective_price' => $this->effectivePrice(),
            'image_url' => $this->mediaUrl($this->image_path),
            'additional_images' => array_map(
                fn ($path) => $this->mediaUrl($path),
                $this->additional_images ?? []
            ),
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'color' => $this->category->color,
            ] : null),
            'in_stock' => ! $this->track_stock || $this->stock > 0,
            'image_focus' => $this->image_focus,
            'variant_groups' => $this->variant_groups ?? [],
            'variants' => $this->whenLoaded('variants', fn () => $this->variants->map(fn ($v) => [
                'id' => $v->id,
                'name' => $v->name,
                'option_values' => $v->option_values,
                'price' => $v->price,
                'stock' => $v->stock,
                'in_stock' => $v->is_active && $v->stock > 0,
            ])->values()),
        ];
    }

    private function mediaUrl(?string $path): ?string
    {
        return MediaUrl::public($path);
    }
}
