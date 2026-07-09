<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use App\Models\Plan;
use App\Support\StoreContext;
use Illuminate\Http\JsonResponse;

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
