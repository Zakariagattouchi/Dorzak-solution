<?php

namespace App\Models;

use App\Enums\StockMovementType;
use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use BelongsToStore;

    public const UPDATED_AT = null; // append-only

    protected $fillable = [
        'store_id', 'product_id', 'variant_id', 'type',
        'quantity_change', 'stock_after', 'order_id', 'user_id', 'note',
    ];

    protected function casts(): array
    {
        return [
            'type' => StockMovementType::class,
            'quantity_change' => 'integer',
            'stock_after' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
