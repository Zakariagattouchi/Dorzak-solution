<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use BelongsToStore, HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id', 'category_id', 'name', 'name_ar', 'description', 'description_ar', 'price', 'reduced_price', 'cost',
        'sku', 'unit', 'image_path', 'additional_images', 'image_focus', 'label_name', 'label_color', 'taxable', 'track_stock',
        'stock', 'min_stock', 'show_in_online_store', 'is_featured', 'is_active', 'variant_groups',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'reduced_price' => 'decimal:2',
            'cost' => 'decimal:2',
            'taxable' => 'boolean',
            'track_stock' => 'boolean',
            'show_in_online_store' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'stock' => 'integer',
            'min_stock' => 'integer',
            'variant_groups' => 'array',
            'additional_images' => 'array',
            'image_focus' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order')->orderBy('id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** The price actually charged (sale price when present and below the base price). */
    public function effectivePrice(): string
    {
        $reduced = $this->reduced_price;

        return ($reduced !== null && (float) $reduced > 0 && (float) $reduced < (float) $this->price)
            ? (string) $reduced
            : (string) $this->price;
    }

    public function stockStatus(): string
    {
        if (! $this->track_stock) {
            return 'IN_STOCK';
        }
        if ($this->stock <= 0) {
            return 'OUT';
        }

        return $this->stock > $this->min_stock ? 'IN_STOCK' : 'LOW';
    }

    public function hasVariants(): bool
    {
        return $this->variants()->exists();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }
        $like = '%'.str_replace('%', '\%', $term).'%';

        return $query->where(function (Builder $q) use ($like) {
            $q->where('name', 'like', $like)
                ->orWhere('sku', 'like', $like)
                ->orWhereHas('category', fn (Builder $c) => $c->where('name', 'like', $like));
        });
    }
}
