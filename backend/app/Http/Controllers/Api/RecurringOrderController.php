<?php

namespace App\Http\Controllers\Api;

use App\Enums\PlanFeature;
use App\Http\Controllers\Controller;
use App\Models\OrderSubscription;
use App\Services\PlanGate;
use App\Services\RecurringOrderService;
use App\Support\StoreContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Recurring-order subscriptions (premium — PlanFeature::RECURRING_ORDERS). */
class RecurringOrderController extends Controller
{
    public function __construct(
        private readonly PlanGate $plans,
        private readonly StoreContext $context,
        private readonly RecurringOrderService $recurring,
    ) {}

    public function index(): JsonResponse
    {
        $rows = OrderSubscription::orderByDesc('id')->get()->map(fn (OrderSubscription $s) => [
            'id' => $s->id, 'customer_id' => $s->customer_id, 'cadence' => $s->cadence,
            'status' => $s->status, 'next_run_at' => $s->next_run_at?->toIso8601String(),
        ]);

        return response()->json(['subscriptions' => $rows]);
    }

    public function store(Request $request): JsonResponse
    {
        $store = $this->context->store();
        $this->plans->ensure($store, PlanFeature::RECURRING_ORDERS);
        abort_unless($request->user()->can('orders.create'), 403);

        $data = $request->validate([
            'customer_id' => ['required', 'integer'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'cadence' => ['required', 'in:WEEKLY,MONTHLY'],
            'next_run_at' => ['nullable', 'date'],
        ]);

        $sub = $this->recurring->create($store, $data);

        return response()->json(['id' => $sub->id], 201);
    }

    public function update(Request $request, OrderSubscription $subscription): JsonResponse
    {
        abort_unless($subscription->store_id === $this->context->store()->id, 404);
        abort_unless($request->user()->can('orders.create'), 403);

        $data = $request->validate(['status' => ['required', 'in:active,paused,cancelled']]);
        $subscription->update(['status' => $data['status']]);

        return response()->json(['ok' => true, 'status' => $subscription->status]);
    }
}
