<?php

namespace App\Http\Controllers\Api\Platform;

use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * GET /platform/overview — the super-admin dashboard: fleet health at a glance.
 * Aggregate metrics only; no tenant-private commercial data crosses this line.
 */
class OverviewController extends Controller
{
    public function index(): JsonResponse
    {
        $storesTotal = Store::count();
        $suspended = Store::whereNotNull('suspended_at')->count();

        // Plan distribution by code (only plans that have subscribers appear).
        $byPlan = Subscription::query()
            ->selectRaw('plan_id, count(*) as total')
            ->groupBy('plan_id')
            ->pluck('total', 'plan_id');

        $planRows = Plan::orderBy('sort_order')->get()->map(fn (Plan $p) => [
            'code' => $p->code,
            'name_en' => $p->name_en,
            'price' => (float) $p->price,
            'stores' => (int) ($byPlan[$p->id] ?? 0),
        ]);

        // MRR estimate: active paid subscriptions × their plan's monthly price.
        $mrr = Subscription::query()
            ->where('status', SubscriptionStatus::ACTIVE->value)
            ->join('plans', 'plans.id', '=', 'subscriptions.plan_id')
            ->where('plans.price', '>', 0)
            ->sum('plans.price');

        $trialsActive = Subscription::where('status', SubscriptionStatus::TRIALING->value)->count();

        // Signups per day for the last 14 days (store created_at).
        $since = now()->subDays(13)->startOfDay();
        $signupsByDay = Store::where('created_at', '>=', $since)
            ->get(['created_at'])
            ->groupBy(fn (Store $s) => $s->created_at->toDateString())
            ->map->count();

        $signups = [];
        for ($i = 13; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $signups[] = ['date' => $day, 'count' => (int) ($signupsByDay[$day] ?? 0)];
        }

        return response()->json([
            'data' => [
                'stores' => [
                    'total' => $storesTotal,
                    'active' => $storesTotal - $suspended,
                    'suspended' => $suspended,
                ],
                'users_total' => User::count(),
                'platform_admins' => User::where('is_platform_admin', true)->count(),
                'mrr_estimate' => (float) $mrr,
                'trials_active' => $trialsActive,
                'plan_distribution' => $planRows,
                'signups_last_14_days' => $signups,
            ],
        ]);
    }
}
