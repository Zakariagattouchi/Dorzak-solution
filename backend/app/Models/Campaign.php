<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Model;

/** A marketing campaign sent to customers. */
class Campaign extends Model
{
    use BelongsToStore;

    protected $fillable = [
        'store_id', 'subject', 'body', 'audience', 'status', 'scheduled_at', 'sent_at', 'sent_count',
    ];

    protected function casts(): array
    {
        return [
            'audience' => 'array',
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
            'sent_count' => 'integer',
        ];
    }
}
