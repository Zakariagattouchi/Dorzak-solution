<?php

namespace App\Http\Controllers\Api;

use App\Enums\PlanFeature;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\CouponService;
use App\Services\PlanGate;
use App\Support\StoreContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Coupon management (premium — PlanFeature::COUPONS). Listing is open to any
 * member; creating/deleting requires the capability and settings.manage.
 */
class CouponController extends Controller
{
    public function __construct(
        private readonly PlanGate $plans,
        private readonly StoreContext $context,
        private readonly CouponService $coupons,
    ) {}

    public function index(): JsonResponse
    {
        $rows = Coupon::orderByDesc('id')->get()->map(fn (Coupon $c) => [
            'id' => $c->id, 'code' => $c->code, 'type' => $c->type, 'value' => (float) $c->value,
            'min_order' => (float) $c->min_order, 'usage_limit' => $c->usage_limit,
            'used_count' => $c->used_count, 'active' => $c->active,
            'expires_at' => $c->expires_at?->toDateString(),
        ]);

        return response()->json(['coupons' => $rows]);
    }

    public function store(Request $request): JsonResponse
    {
        $store = $this->context->store();
        $this->plans->ensure($store, PlanFeature::COUPONS);
        abort_unless($request->user()->can('settings.manage'), 403);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:60'],
            'type' => ['required', 'in:PERCENT,FIXED'],
            'value' => ['required', 'numeric', 'min:0'],
            'min_order' => ['nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $coupon = $this->coupons->create($store, $data);

        return response()->json(['id' => $coupon->id, 'code' => $coupon->code], 201);
    }

    public function destroy(Request $request, Coupon $coupon): JsonResponse
    {
        abort_unless($coupon->store_id === $this->context->store()->id, 404);
        abort_unless($request->user()->can('settings.manage'), 403);

        $coupon->delete();

        return response()->json(['ok' => true]);
    }
}
