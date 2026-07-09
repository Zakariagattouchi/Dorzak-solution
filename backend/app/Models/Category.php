<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use BelongsToStore, HasFactory;

    protected $fillable = ['store_id', 'name', 'color', 'image_path', 'description', 'sort_order'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
