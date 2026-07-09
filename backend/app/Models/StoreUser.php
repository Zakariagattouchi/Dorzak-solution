<?php

namespace App\Models;

use App\Enums\StaffRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Membership pivot (store_user). Modelled as a full Pivot with an incrementing id
 * so it can be addressed directly by the staff endpoints (TP-03).
 */
class StoreUser extends Pivot
{
    public $incrementing = true;

    protected $table = 'store_user';

    protected $fillable = ['store_id', 'user_id', 'role', 'is_active', 'joined_at'];

    protected function casts(): array
    {
        return [
            'role' => StaffRole::class,
            'is_active' => 'boolean',
            'joined_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }
}
