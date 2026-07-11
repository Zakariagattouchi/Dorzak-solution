<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Model;

/** An append-only store-credit ledger line. */
class WalletEntry extends Model
{
    use BelongsToStore;

    public $timestamps = false;

    protected $fillable = ['store_id', 'customer_id', 'amount', 'reason', 'created_at'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }
}
