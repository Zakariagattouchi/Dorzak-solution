<?php

namespace App\Http\Controllers\Api;

use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use App\Models\Plan;
use App\Services\PlanGate;
use App\Support\StoreContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(private readonly StoreContext $context) {}

    /** GET /subscription — readable by any member (Billing + Settings tab). */
    public function show(): SubscriptionResource
    {
        $store = $this->context->store();

        $subscription = $store->subscription()
            ->firstOrCreate([], ['plan_id' => Plan::default()?->id])
            ->load(['store', 'plan.featureLimits']);

        return new SubscriptionResource($subscription);
    }

    /**
     * POST /subscription/trial — owner only; one free trial per store, ever.
     * Points the subscription at the trial plan; PlanGate unlocks its features
     * immediately. `subscriptions:expire-trials` reverts it when time runs out.
     */
    public function startTrial(Request $request, PlanGate $plans): SubscriptionResource
    {
        abort_unless($request->user()->can('billing.manage'), 403);

        $data = $request->validate(['plan_id' => 'required|integer|exists:plans,id']);

        $store = $this->context->store();
        $plan = Plan::findOrFail($data['plan_id']);

        abort_if(! $plan->is_active || $plan->is_default || $plan->trial_days < 1, 422, 'This plan does not offer a trial.');

        $subscription = $store->subscription()->firstOrCreate([], ['plan_id' => Plan::default()?->id]);

        abort_if($subscription->trial_used_at !== null, 422, 'Your free trial has already been used.');
        abort_unless($subscription->plan?->is_default ?? true, 422, 'Trials are only available from the free plan.');

        $subscription->update([
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::TRIALING,
            'trial_ends_at' => now()->addDays($plan->trial_days),
            'trial_used_at' => now(),
        ]);

        $plans->forget($store);

        return new SubscriptionResource($subscription->load(['store', 'plan.featureLimits']));
    }

    /** POST /subscription/portal — owner only; provider portal link (stub until TP-09/Stripe). */
    public function portal(): JsonResponse
    {
        abort_unless(request()->user()->can('billing.manage'), 403);

        return response()->json([
            'data' => ['url' => (string) config('services.billing.portal_url', config('app.frontend_url').'/billing')],
        ]);
    }

    /** GET /subscription/invoice/latest — owner only; wired to a provider in TP-09. */
    public function invoiceLatest(): JsonResponse
    {
        abort_unless(request()->user()->can('billing.manage'), 403);

        return response()->json([
            'message' => 'Invoice downloads are available once billing is connected.',
            'code' => 'BILLING_NOT_CONNECTED',
        ], 501);
    }
}
