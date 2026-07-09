<?php

namespace App\Http\Controllers\Api\Platform;

use App\Enums\PlanFeature;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /** GET /platform/plan-features — the capability catalogue for the plan editor. */
    public function catalog(): JsonResponse
    {
        return response()->json(['data' => PlanFeature::catalog()]);
    }

    public function index(): JsonResponse
    {
        $plans = Plan::with('featureLimits')->orderBy('sort_order')->get();

        return response()->json(['data' => $plans->map(fn (Plan $p) => $this->planShape($p))]);
    }

    public function show(Plan $plan): JsonResponse
    {
        $plan->load('featureLimits');

        return response()->json(['data' => $this->planShape($plan)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => 'required|string|max:32|unique:plans,code',
            'name_en' => 'required|string|max:60',
            'name_ar' => 'required|string|max:60',
            'description_en' => 'nullable|string|max:255',
            'description_ar' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly,once',
            'trial_days' => 'integer|min:0|max:365',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'features' => 'array',
            'features.*.feature' => 'required|string|in:'.$this->featureKeys(),
            'features.*.limit_value' => 'nullable|integer|min:1',
        ]);

        $plan = Plan::create($data);
        $this->persistFeatures($plan, $data['features'] ?? []);
        $plan->load('featureLimits');

        return response()->json(['data' => $this->planShape($plan)], 201);
    }

    public function update(Request $request, Plan $plan): JsonResponse
    {
        $data = $request->validate([
            'code' => 'sometimes|string|max:32|unique:plans,code,'.$plan->id,
            'name_en' => 'sometimes|string|max:60',
            'name_ar' => 'nullable|string|max:60',
            'description_en' => 'nullable|string|max:255',
            'description_ar' => 'nullable|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'billing_cycle' => 'sometimes|in:monthly,yearly,once',
            'trial_days' => 'integer|min:0|max:365',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'features' => 'array',
            'features.*.feature' => 'required|string|in:'.$this->featureKeys(),
            'features.*.limit_value' => 'nullable|integer|min:1',
        ]);

        $plan->update($data);

        if (array_key_exists('features', $data)) {
            $plan->featureLimits()->delete();
            $this->persistFeatures($plan, $data['features']);
        }

        $plan->load('featureLimits');

        return response()->json(['data' => $this->planShape($plan)]);
    }

    public function destroy(Plan $plan): JsonResponse
    {
        if ($plan->is_default) {
            abort(422, 'The default plan cannot be deleted.');
        }

        $plan->featureLimits()->delete();
        $plan->delete();

        return response()->json(status: 204);
    }

    /** Comma-joined enum values for the `in:` validation rule. */
    private function featureKeys(): string
    {
        return implode(',', array_map(fn (PlanFeature $f) => $f->value, PlanFeature::cases()));
    }

    /**
     * Persist a plan's feature rows, keeping the last entry per feature so a
     * duplicated key in the payload can't trip the (plan_id, feature) unique key.
     *
     * @param  array<int, array{feature:string, limit_value?:int|null}>  $features
     */
    private function persistFeatures(Plan $plan, array $features): void
    {
        $byKey = [];
        foreach ($features as $f) {
            $byKey[$f['feature']] = $f['limit_value'] ?? null;
        }

        foreach ($byKey as $feature => $limit) {
            $plan->featureLimits()->create(['feature' => $feature, 'limit_value' => $limit]);
        }
    }

    private function planShape(Plan $plan): array
    {
        return [
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
            'is_active' => $plan->is_active,
            'sort_order' => $plan->sort_order,
            'features' => $plan->featureLimits->map(fn ($f) => [
                'feature' => $f->feature instanceof PlanFeature ? $f->feature->value : $f->feature,
                'limit_value' => $f->limit_value,
            ]),
        ];
    }
}
