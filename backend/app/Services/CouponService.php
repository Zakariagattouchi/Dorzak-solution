<?php

namespace App\Services;

use App\Enums\PlanFeature;
use App\Exceptions\DomainConflictException;
use App\Models\Coupon;
use App\Models\Store;

/**
 * Customer-facing coupon codes (premium — PlanFeature::COUPONS). Quoting a code
 * against a subtotal validates it (active, unexpired, within usage limit, meets
 * minimum) and returns the discount; redeeming increments the usage counter.
 */
class CouponService
{
    public function __construct(private readonly PlanGate $plans) {}

    /** @param array<string, mixed> $data */
    public function create(Store $store, array $data): Coupon
    {
        $this->plans->ensure($store, PlanFeature::COUPONS);

        return Coupon::create([
            'store_id' => $store->id,
            'code' => strtoupper(trim($data['code'])),
            'type' => ($data['type'] ?? 'PERCENT') === 'FIXED' ? 'FIXED' : 'PERCENT',
            'value' => (float) $data['value'],
            'min_order' => (float) ($data['min_order'] ?? 0),
            'max_discount' => isset($data['max_discount']) ? (float) $data['max_discount'] : null,
            'usage_limit' => $data['usage_limit'] ?? null,
            'active' => $data['active'] ?? true,
            'expires_at' => $data['expires_at'] ?? null,
        ]);
    }

    /**
     * Validate a code against a subtotal and return the discount.
     *
     * @return array{coupon: Coupon, discount: float}
     *
     * @throws DomainConflictException
     */
    public function quote(Store $store, string $code, float $subtotal): array
    {
        $coupon = Coupon::where('store_id', $store->id)
            ->whereRaw('UPPER(code) = ?', [strtoupper(trim($code))])
            ->first();

        if ($coupon === null || ! $coupon->active) {
            throw new DomainConflictException('COUPON_INVALID', 'That coupon code is not valid.');
        }

        if ($coupon->expires_at !== null && $coupon->expires_at->isPast()) {
            throw new DomainConflictException('COUPON_EXPIRED', 'That coupon has expired.');
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            throw new DomainConflictException('COUPON_EXHAUSTED', 'That coupon has reached its usage limit.');
        }

        if ($subtotal < (float) $coupon->min_order) {
            throw new DomainConflictException('COUPON_MIN_ORDER', 'Order total is below this coupon’s minimum.');
        }

        $discount = $coupon->type === 'FIXED'
            ? (float) $coupon->value
            : round($subtotal * (float) $coupon->value / 100, 2);

        if ($coupon->max_discount !== null) {
            $discount = min($discount, (float) $coupon->max_discount);
        }

        return ['coupon' => $coupon, 'discount' => round(min($discount, $subtotal), 2)];
    }

    public function redeem(Coupon $coupon): void
    {
        $coupon->increment('used_count');
    }
}
