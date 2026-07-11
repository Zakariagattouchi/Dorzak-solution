<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Model;

/** A customer's store-credit balance at one store. */
class WalletAccount extends Model
{
    use BelongsToStore;

    protected $fillable = ['store_id', 'customer_id', 'balance'];

    protected function casts(): array
    {
        return ['balance' => 'decimal:2'];
    }
}
