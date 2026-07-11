<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Model;

/** A prepaid gift card that credits a customer's wallet on redemption. */
class GiftCard extends Model
{
    use BelongsToStore;

    protected $fillable = ['store_id', 'code', 'amount', 'status', 'redeemed_by_customer_id', 'redeemed_at'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'redeemed_at' => 'datetime',
        ];
    }
}
