<?php

namespace App\Models;

use App\Enums\PlanFeature;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One granted capability on a plan (table `plan_features`). Presence = enabled;
 * limit_value caps limit-kind features (null = unlimited). Named *Limit to avoid
 * colliding with the PlanFeature capability enum.
 */
class PlanFeatureLimit extends Model
{
    protected $table = 'plan_features';

    protected $fillable = ['plan_id', 'feature', 'limit_value'];

    protected function casts(): array
    {
        return [
            'feature' => PlanFeature::class,
            'limit_value' => 'integer',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
