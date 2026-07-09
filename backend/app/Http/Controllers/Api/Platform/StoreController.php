<?php

namespace App\Http\Controllers\Api\Platform;

use App\Enums\OrderStatus;
use App\Enums\StaffRole;
use App\Http\Controllers\Controller;
use App\Models\PlatformAuditLog;
use App\Models\Store;
use App\Services\PlanGate;
use App\Support\CatalogCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    /** Store detail with aggregate counts (no private orders/customers leave here). */
    public function show(Store $store): JsonResponse
    {
        $store->load('subscription.plan');

        $owner = $store->memberships()
            ->where('role', StaffRole::OWNER->value)
            ->with('user:id,name,email')
            ->orderBy('id')
            ->first();

        return response()->json([
            'data' => array_merge($this->storeShape($store), [
                'owner' => $owner?->user ? [
                    'id' => $owner->user->id,
                    'name' => $owner->user->name,
                    'email' => $owner->user->email,
                ] : null,
                'metrics' => [
                    'staff' => $store->memberships()->count(),
                    'products' => $store->products()->count(),
                    'customers' => $store->customers()->count(),
                    'orders' => $store->orders()->count(),
                    'revenue' => (float) $store->orders()->where('status', OrderStatus::COMPLETE->value)->sum('total'),
                ],
            ]),
        ]);
    }

    public function suspend(Request $request, Store $store, PlanGate $plans): JsonResponse
    {
        if ($store->suspended_at !== null) {
            return response()->json(['data' => $this->storeShape($store->load('subscription.plan'))]);
        }

        $store->update(['suspended_at' => now()]);
        $plans->forget($store);
        $this->audit($request, 'store.suspend', $store);

        return response()->json(['data' => $this->storeShape($store->fresh()->load('subscription.plan'))]);
    }

    public function reactivate(Request $request, Store $store, PlanGate $plans): JsonResponse
    {
        $store->update(['suspended_at' => null]);
        $plans->forget($store);
        $this->audit($request, 'store.reactivate', $store);

        return response()->json(['data' => $this->storeShape($store->fresh()->load('subscription.plan'))]);
    }

    public function assignPlan(Request $request, Store $store, PlanGate $plans): JsonResponse
    {
        $data = $request->validate(['plan_id' => 'required|exists:plans,id']);

        $store->subscription()->firstOrCreate([], [])->update(['plan_id' => $data['plan_id']]);
        $store->load('subscription.plan.featureLimits');
        $plans->forget($store);
        // The public store card caches plan-dependent capability (delivery_mode).
        CatalogCache::bump($store->storefrontSetting?->slug);
        $this->audit($request, 'store.assign_plan', $store, ['plan_id' => (int) $data['plan_id']]);

        return response()->json(['data' => $this->storeShape($store->load('subscription.plan'))]);
    }

    /** Permanently delete a store and all its tenant data. Irreversible. */
    public function destroy(Request $request, Store $store): JsonResponse
    {
        $request->validate(['confirm_name' => 'required|string']);

        abort_unless(
            $request->string('confirm_name')->toString() === $store->name,
            422,
            'The typed store name does not match.',
        );

        $label = $store->name;

        DB::transaction(fn () => $store->delete());

        PlatformAuditLog::record($request->user(), 'store.delete', null, $label, ['store_id' => $store->id], $request->ip());

        return response()->json(status: 204);
    }

    private function audit(Request $request, string $action, Store $store, array $meta = []): void
    {
        PlatformAuditLog::record($request->user(), $action, $store, $store->name, $meta, $request->ip());
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
