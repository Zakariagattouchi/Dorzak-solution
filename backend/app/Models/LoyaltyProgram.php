<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Model;

/** Per-store loyalty configuration (earn rate + redemption rule). */
class LoyaltyProgram extends Model
{
    use BelongsToStore;

    protected $fillable = [
        'store_id', 'enabled', 'earn_points_per_currency', 'redeem_points', 'redeem_value',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'earn_points_per_currency' => 'integer',
            'redeem_points' => 'integer',
            'redeem_value' => 'decimal:2',
        ];
    }
}
