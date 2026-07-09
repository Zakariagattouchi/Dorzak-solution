<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\PlanFeatureLimit;
use App\Support\DefaultPlans;
use Illuminate\Database\Seeder;

/**
 * Idempotently (re)creates the canonical plans from DefaultPlans. The backfill
 * migration already seeds these on existing databases; this keeps `migrate:fresh
 * --seed` and future squashes self-sufficient. Safe to re-run. See doc 13 §2.
 */
class PlanSeeder extends Seeder
{
    public function run(): void
    {
        foreach (DefaultPlans::definitions() as $def) {
            $plan = Plan::updateOrCreate(
                ['code' => $def['code']],
                [
                    'name_en' => $def['name_en'],
                    'name_ar' => $def['name_ar'],
                    'description_en' => $def['description_en'],
                    'description_ar' => $def['description_ar'],
                    'price' => $def['price'],
                    'billing_cycle' => $def['billing_cycle'],
                    'is_default' => $def['is_default'],
                    'is_active' => $def['is_active'],
                    'sort_order' => $def['sort_order'],
                ],
            );

            foreach ($def['features'] as $feature => $limit) {
                PlanFeatureLimit::updateOrCreate(
                    ['plan_id' => $plan->id, 'feature' => $feature],
                    ['limit_value' => $limit],
                );
            }
        }
    }
}
