<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A customer's recurring-order subscription. */
class OrderSubscription extends Model
{
    use BelongsToStore;

    protected $fillable = [
        'store_id', 'customer_id', 'items', 'cadence', 'status', 'next_run_at', 'last_run_at',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'next_run_at' => 'datetime',
            'last_run_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
