<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Model;

/** Per-store referral configuration. */
class ReferralProgram extends Model
{
    use BelongsToStore;

    protected $fillable = ['store_id', 'enabled', 'referrer_reward', 'referee_reward'];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'referrer_reward' => 'decimal:2',
            'referee_reward' => 'decimal:2',
        ];
    }
}
