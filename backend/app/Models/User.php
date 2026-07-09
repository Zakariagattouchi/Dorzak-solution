<?php

namespace App\Models;

use App\Enums\StaffRole;
use App\Support\StoreContext;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'is_platform_admin'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_platform_admin' => 'boolean',
        ];
    }

    /** Stores this user belongs to, with role + active flag on the pivot. */
    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class)
            ->using(StoreUser::class)
            ->withPivot(['role', 'is_active', 'joined_at'])
            ->withTimestamps();
    }

    /** Membership rows for this user (one per store). */
    public function memberships(): HasMany
    {
        return $this->hasMany(StoreUser::class);
    }

    /**
     * The membership for a specific store, or the user's first store when none is
     * given (the common case today — one store per account). Does NOT consult
     * StoreContext: this method is what *populates* the context, so reading it back
     * here would return a stale store between requests. Returns the row regardless
     * of is_active so callers (EnsureStoreMember) can tell "disabled" from "no store".
     */
    public function currentMembership(?int $storeId = null): ?StoreUser
    {
        $query = $this->memberships()->with('store')->orderBy('id');

        return $storeId !== null
            ? $query->where('store_id', $storeId)->first()
            : $query->first();
    }

    public function currentRole(): ?StaffRole
    {
        return app(StoreContext::class)->role()
            ?? $this->currentMembership()?->role;
    }
}
