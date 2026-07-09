<?php

namespace App\Models;

use App\Enums\StaffRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffInvitation extends Model
{
    protected $fillable = [
        'store_id', 'invited_by', 'name', 'email', 'role', 'token', 'expires_at', 'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'role' => StaffRole::class,
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isPending(): bool
    {
        return $this->accepted_at === null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function scopePending($query)
    {
        return $query->whereNull('accepted_at');
    }
}
