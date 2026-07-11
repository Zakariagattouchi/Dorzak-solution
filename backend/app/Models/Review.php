<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A product review, pending until a merchant approves it. */
class Review extends Model
{
    use BelongsToStore;

    protected $fillable = [
        'store_id', 'product_id', 'customer_id', 'author_name', 'rating', 'comment', 'approved',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'approved' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
