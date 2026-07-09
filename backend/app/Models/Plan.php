<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An operator-configurable subscription plan (doc 13 §2). The set of granted
 * capabilities lives in `featureLimits`; PlanGate reads them to gate the app.
 */
class Plan extends Model
{
    protected $fillable = [
        'code', 'name_en', 'name_ar', 'description_en', 'description_ar',
        'price', 'billing_cycle', 'is_default', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function featureLimits(): HasMany
    {
        return $this->hasMany(PlanFeatureLimit::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /** The forever-free plan new signups land on. */
    public static function default(): ?self
    {
        return static::query()->where('is_default', true)->first();
    }
}
