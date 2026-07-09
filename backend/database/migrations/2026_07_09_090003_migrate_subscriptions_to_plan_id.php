<?php

use App\Support\DefaultPlans;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seeds the canonical plans, then re-points subscriptions from the `plan` string
 * (FREE|PRO|ENTERPRISE) onto a `plan_id` FK and drops the string column. The
 * SubscriptionPlan enum is retired after this. See doc 13 §2.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->seedDefaultPlans();

        Schema::table('subscriptions', function (Blueprint $table) {
            // Nullable + nullOnDelete: an orphaned subscription resolves to the
            // default plan in PlanGate rather than erroring.
            $table->foreignId('plan_id')->nullable()->after('store_id')
                ->constrained('plans')->nullOnDelete();
        });

        // Backfill existing subscriptions from their plan code.
        foreach (DB::table('plans')->pluck('id', 'code') as $code => $planId) {
            DB::table('subscriptions')->where('plan', $code)->update(['plan_id' => $planId]);
        }

        // Any unmapped rows fall back to the default plan.
        $defaultId = DB::table('plans')->where('is_default', true)->value('id');
        if ($defaultId !== null) {
            DB::table('subscriptions')->whereNull('plan_id')->update(['plan_id' => $defaultId]);
        }

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('plan');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('plan', 12)->default('FREE')->after('store_id');
        });

        // Best-effort restore of the string code from the FK.
        foreach (DB::table('plans')->pluck('code', 'id') as $planId => $code) {
            DB::table('subscriptions')->where('plan_id', $planId)->update(['plan' => $code]);
        }

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('plan_id');
        });
    }

    private function seedDefaultPlans(): void
    {
        foreach (DefaultPlans::definitions() as $def) {
            $planId = DB::table('plans')->where('code', $def['code'])->value('id');

            if ($planId === null) {
                $planId = DB::table('plans')->insertGetId([
                    'code' => $def['code'],
                    'name_en' => $def['name_en'],
                    'name_ar' => $def['name_ar'],
                    'description_en' => $def['description_en'],
                    'description_ar' => $def['description_ar'],
                    'price' => $def['price'],
                    'billing_cycle' => $def['billing_cycle'],
                    'is_default' => $def['is_default'],
                    'is_active' => $def['is_active'],
                    'sort_order' => $def['sort_order'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($def['features'] as $feature => $limit) {
                DB::table('plan_features')->updateOrInsert(
                    ['plan_id' => $planId, 'feature' => $feature],
                    ['limit_value' => $limit, 'updated_at' => now(), 'created_at' => now()],
                );
            }
        }
    }
};
