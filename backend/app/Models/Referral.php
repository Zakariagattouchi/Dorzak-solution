<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A referral link between two customers. */
class Referral extends Model
{
    use BelongsToStore;

    protected $fillable = [
        'store_id', 'referrer_customer_id', 'referred_customer_id', 'code', 'status', 'reward_order_id',
    ];

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'referrer_customer_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'referred_customer_id');
    }
}
