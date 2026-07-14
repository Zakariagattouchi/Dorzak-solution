<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
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
        $notCancelled = fn ($q) => $q->where('status', '!=', OrderStatus::CANCELLED);

        $rows = Coupon::query()
            ->withCount(['orders as orders' => $notCancelled])
            ->withSum(['orders as revenue' => $notCancelled], 'total')
            ->withSum(['orders as discount_given' => $notCancelled], 'discount')
            ->orderByDesc('id')->get()->map(fn (Coupon $c) => [
                'id' => $c->id, 'code' => $c->code, 'type' => $c->type, 'value' => (float) $c->value,
                'min_order' => (float) $c->min_order, 'max_discount' => $c->max_discount !== null ? (float) $c->max_discount : null,
                'usage_limit' => $c->usage_limit, 'used_count' => $c->used_count, 'active' => $c->active,
                'expires_at' => $c->expires_at?->toDateString(),
                'orders' => (int) $c->orders, 'revenue' => (float) ($c->revenue ?? 0), 'discount_given' => (float) ($c->discount_given ?? 0),
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

    /** Update a coupon — primarily the active toggle, plus editable limits. */
    public function update(Request $request, Coupon $coupon): JsonResponse
    {
        abort_unless($coupon->store_id === $this->context->store()->id, 404);
        abort_unless($request->user()->can('settings.manage'), 403);

        $data = $request->validate([
            'active' => ['sometimes', 'boolean'],
            'min_order' => ['sometimes', 'numeric', 'min:0'],
            'max_discount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'usage_limit' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'expires_at' => ['sometimes', 'nullable', 'date'],
        ]);

        $coupon->update($data);

        return response()->json(['ok' => true, 'active' => $coupon->active]);
    }

    public function destroy(Request $request, Coupon $coupon): JsonResponse
    {
        abort_unless($coupon->store_id === $this->context->store()->id, 404);
        abort_unless($request->user()->can('settings.manage'), 403);

        $coupon->delete();

        return response()->json(['ok' => true]);
    }
}
