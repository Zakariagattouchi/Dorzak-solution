<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;

/**
 * GET /plans — the upgrade/pricing catalog shown to merchants (any member).
 * Only active plans; the platform admin manages the full set via /platform/plans.
 */
class PlanCatalogController extends Controller
{
    public function index(): JsonResponse
    {
        $plans = Plan::with('featureLimits')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => $plans->map(fn (Plan $plan) => [
                'id' => $plan->id,
                'code' => $plan->code,
                'name_en' => $plan->name_en,
                'name_ar' => $plan->name_ar,
                'description_en' => $plan->description_en,
                'description_ar' => $plan->description_ar,
                'price' => (float) $plan->price,
                'billing_cycle' => $plan->billing_cycle,
                'trial_days' => $plan->trial_days,
                'is_default' => $plan->is_default,
                'features' => $plan->featureLimits->map(fn ($row) => [
                    'feature' => $row->feature->value,
                    'limit' => $row->limit_value,
                ])->values(),
            ]),
        ]);
    }
}
