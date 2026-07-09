<?php

namespace App\Http\Resources;

use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProductVariant
 */
class ProductVariantResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'option_values' => $this->option_values,
            'price' => $this->price,
            'stock' => $this->stock,
            'sku' => $this->sku,
            'is_active' => (bool) $this->is_active,
        ];
    }
}
