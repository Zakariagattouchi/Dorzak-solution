<?php

use App\Support\DefaultPlans;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Free-trial mechanics (doc 13 §2 addendum). A plan advertises `trial_days`;
 * a store may start ONE trial ever (`trial_used_at`), which points its
 * subscription at the trial plan until `trial_ends_at`, when the daily
 * `subscriptions:expire-trials` command reverts it to the default plan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            $table->unsignedSmallInteger('trial_days')->default(0)->after('billing_cycle');
        });

        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->timestamp('trial_ends_at')->nullable()->after('renews_at');
            $table->timestamp('trial_used_at')->nullable()->after('trial_ends_at');
        });

        // Backfill the canonical trial lengths onto the seeded plans (the plans
        // backfill migration ran before this column existed).
        foreach (DefaultPlans::definitions() as $def) {
            DB::table('plans')
                ->where('code', $def['code'])
                ->update(['trial_days' => $def['trial_days'] ?? 0]);
        }
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            $table->dropColumn('trial_days');
        });

        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->dropColumn(['trial_ends_at', 'trial_used_at']);
        });
    }
};
