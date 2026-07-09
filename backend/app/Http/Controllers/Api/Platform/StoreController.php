<?php

namespace App\Http\Controllers\Api\Platform;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\PlanGate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Store::with(['subscription.plan'])
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('plan'), fn ($q) => $q->whereHas('subscription', fn ($s) => $s->whereHas('plan', fn ($p) => $p->where('code', $request->string('plan')))))
            ->when($request->input('status') === 'suspended', fn ($q) => $q->whereNotNull('suspended_at'))
            ->when($request->input('status') === 'active', fn ($q) => $q->whereNull('suspended_at'))
            ->orderBy('id');

        $stores = $query->paginate(50);

        return response()->json([
            'data' => $stores->getCollection()->map(fn (Store $s) => $this->storeShape($s)),
            'meta' => ['total' => $stores->total(), 'per_page' => $stores->perPage(), 'current_page' => $stores->currentPage()],
        ]);
    }

    public function suspend(Request $request, Store $store, PlanGate $plans): JsonResponse
    {
        if ($store->suspended_at !== null) {
            return response()->json(['data' => $this->storeShape($store->load('subscription.plan'))]);
        }

        $store->update(['suspended_at' => now()]);
        $plans->forget($store);

        return response()->json(['data' => $this->storeShape($store->fresh()->load('subscription.plan'))]);
    }

    public function reactivate(Request $request, Store $store, PlanGate $plans): JsonResponse
    {
        $store->update(['suspended_at' => null]);
        $plans->forget($store);

        return response()->json(['data' => $this->storeShape($store->fresh()->load('subscription.plan'))]);
    }

    public function assignPlan(Request $request, Store $store, PlanGate $plans): JsonResponse
    {
        $data = $request->validate(['plan_id' => 'required|exists:plans,id']);

        $store->subscription()->firstOrCreate([], [])->update(['plan_id' => $data['plan_id']]);
        $store->load('subscription.plan.featureLimits');
        $plans->forget($store);

        return response()->json(['data' => $this->storeShape($store->load('subscription.plan'))]);
    }

    private function storeShape(Store $store): array
    {
        $plan = $store->subscription?->plan;

        return [
            'id' => $store->id,
            'name' => $store->name,
            'email' => $store->email,
            'country' => $store->country,
            'suspended_at' => $store->suspended_at?->toISOString(),
            'created_at' => $store->created_at?->toISOString(),
            'plan' => $plan ? ['id' => $plan->id, 'code' => $plan->code, 'name_en' => $plan->name_en] : null,
        ];
    }
}
