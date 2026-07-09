<?php

use App\Enums\PlanFeature;
use App\Support\DefaultPlans;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Reconciles the canonical plans onto the expanded PlanFeature catalogue
 * (POS access, photo/category caps, extra channels — doc 13 §1). Idempotent:
 * re-applies each canonical plan's feature map, and guarantees POS_ACCESS on
 * EVERY existing plan so no live store loses its register on deploy.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Re-apply the canonical feature maps (adds the new features; leaves
        //    operator-added rows untouched).
        foreach (DefaultPlans::definitions() as $def) {
            $planId = DB::table('plans')->where('code', $def['code'])->value('id');

            if ($planId === null) {
                continue;
            }

            foreach ($def['features'] as $feature => $limit) {
                DB::table('plan_features')->updateOrInsert(
                    ['plan_id' => $planId, 'feature' => $feature],
                    ['limit_value' => $limit, 'updated_at' => now(), 'created_at' => now()],
                );
            }
        }

        // 2. Safety net: any plan (including custom ones) without POS_ACCESS gets
        //    it, so gating the register can never strand an existing merchant.
        $pos = PlanFeature::POS_ACCESS->value;
        DB::table('plans')->pluck('id')->each(function ($planId) use ($pos) {
            DB::table('plan_features')->updateOrInsert(
                ['plan_id' => $planId, 'feature' => $pos],
                ['limit_value' => null, 'updated_at' => now(), 'created_at' => now()],
            );
        });
    }

    public function down(): void
    {
        // Non-destructive forward migration; nothing to roll back safely.
    }
};
