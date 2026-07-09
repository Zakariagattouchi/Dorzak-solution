<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
class ProductResource extends JsonResource
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
            'cost' => $this->cost,
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'color' => $this->category->color,
            ] : null),
            'category_id' => $this->category_id,
            'sku' => $this->sku,
            'unit' => $this->unit,
            'image_url' => $this->imageUrl(),
            'additional_images' => $this->additional_images ?? [],
            'additional_image_urls' => array_map(
                fn ($path) => MediaUrl::public($path),
                $this->additional_images ?? []
            ),
            'label_name' => $this->label_name,
            'label_color' => $this->label_color,
            'taxable' => (bool) $this->taxable,
            'track_stock' => (bool) $this->track_stock,
            'stock' => $this->stock,
            'min_stock' => $this->min_stock,
            'stock_status' => $this->stockStatus(),
            'show_in_online_store' => (bool) $this->show_in_online_store,
            'is_featured' => (bool) $this->is_featured,
            'is_active' => (bool) $this->is_active,
            'variant_groups' => $this->variant_groups ?? [],
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'image_focus' => $this->image_focus,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    private function imageUrl(): ?string
    {
        return MediaUrl::public($this->image_path);
    }
}
