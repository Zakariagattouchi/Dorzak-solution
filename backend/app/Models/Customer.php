<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use BelongsToStore, HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id', 'name', 'email', 'phone', 'referral_code', 'address', 'address_details', 'city', 'latitude', 'longitude', 'tax_id', 'notes',
        'marketing_consent', 'consented_at',
    ];

    protected function casts(): array
    {
        return [
            'total_orders' => 'integer',
            'total_spent' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'marketing_consent' => 'boolean',
            'consented_at' => 'datetime',
        ];
    }

    /** Setting phone also derives the digits-only phone_normalized used for uniqueness + wa.me. */
    protected function phone(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => [
                'phone' => $value,
                'phone_normalized' => preg_replace('/\D/', '', (string) $value),
            ],
        );
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }
        $like = '%'.str_replace('%', '\%', $term).'%';

        return $query->where(fn (Builder $q) => $q
            ->where('name', 'like', $like)
            ->orWhere('email', 'like', $like)
            ->orWhere('phone', 'like', $like));
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
