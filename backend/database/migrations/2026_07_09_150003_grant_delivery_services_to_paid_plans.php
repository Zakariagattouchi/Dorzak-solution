<?php

use App\Enums\PlanFeature;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * DELIVERY_SERVICES becomes a hard-enforced capability (gates access to
 * plan-gated delivery providers). Guarantee the row on every non-default
 * (paid) plan so the flip doesn't silently strip the perk from existing
 * paid tenants. Same pattern as 2026_07_09_140001_expand_default_plan_features.
 */
return new class extends Migration
{
    public function up(): void
    {
        $feature = PlanFeature::DELIVERY_SERVICES->value;

        DB::table('plans')->where('is_default', false)->pluck('id')->each(function ($planId) use ($feature) {
            DB::table('plan_features')->updateOrInsert(
                ['plan_id' => $planId, 'feature' => $feature],
                ['limit_value' => null, 'updated_at' => now(), 'created_at' => now()],
            );
        });
    }

    public function down(): void
    {
        // Non-destructive forward migration; nothing to roll back safely.
    }
};
