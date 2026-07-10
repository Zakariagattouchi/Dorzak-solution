<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A platform-global delivery pricing formula (no BelongsToStore — the super
 * admin manages these; every store quotes against the same set).
 * fee = max(min_fee, base_fee + per_km_fee × distance_km), within max_radius_km.
 */
class DeliveryProvider extends Model
{
    /** The Dorzak network carrier — priced by the delivery system, dispatchable. */
    public const KIND_NETWORK = 'network';

    /** A third-party carrier we merely price-compare against (Uber, Snoonu, …). */
    public const KIND_COMPARATOR = 'comparator';

    protected $fillable = [
        'name', 'code', 'kind', 'base_fee', 'per_km_fee', 'min_fee', 'max_radius_km',
        'is_plan_gated', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'base_fee' => 'decimal:2',
            'per_km_fee' => 'decimal:2',
            'min_fee' => 'decimal:2',
            'max_radius_km' => 'decimal:2',
            'is_plan_gated' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeNetwork(Builder $query): Builder
    {
        return $query->where('kind', self::KIND_NETWORK);
    }

    public function scopeComparators(Builder $query): Builder
    {
        return $query->where('kind', self::KIND_COMPARATOR);
    }

    /** Only the network carrier can be sent to the driver board. */
    public function isNetwork(): bool
    {
        return $this->kind === self::KIND_NETWORK;
    }

    /** Formula fee for a given distance (radius NOT checked here). */
    public function feeFor(float $distanceKm): float
    {
        return round(max((float) $this->min_fee, (float) $this->base_fee + (float) $this->per_km_fee * $distanceKm), 2);
    }
}
