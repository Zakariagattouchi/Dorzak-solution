<?php

namespace App\Http\Resources\Public;

use App\Models\Category;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Category */
class PublicCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'image_url' => $this->mediaUrl($this->image_path),
        ];
    }

    private function mediaUrl(?string $path): ?string
    {
        return MediaUrl::public($path);
    }
}
