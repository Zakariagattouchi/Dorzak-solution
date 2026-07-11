<?php

namespace App\Services;

use App\Exceptions\DomainConflictException;
use App\Models\Customer;
use App\Models\LoyaltyAccount;
use App\Models\LoyaltyProgram;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Support\Facades\DB;

/**
 * Points-per-spend loyalty. Configuration is per store; points accrue on
 * completed orders and redeem at checkout for a discount. The premium plan
 * gate (PlanFeature::LOYALTY) is applied at the controller boundary.
 */
class LoyaltyService
{
    /** @param array<string, mixed> $data */
    public function configure(Store $store, array $data): LoyaltyProgram
    {
        return LoyaltyProgram::updateOrCreate(
            ['store_id' => $store->id],
            [
                'enabled' => $data['enabled'] ?? true,
                'earn_points_per_currency' => (int) ($data['earn_points_per_currency'] ?? 1),
                'redeem_points' => (int) ($data['redeem_points'] ?? 100),
                'redeem_value' => (float) ($data['redeem_value'] ?? 5),
            ],
        );
    }

    public function program(Store $store): ?LoyaltyProgram
    {
        return LoyaltyProgram::firstWhere('store_id', $store->id);
    }

    public function balance(Customer $customer): int
    {
        return (int) (LoyaltyAccount::where('customer_id', $customer->id)->value('points') ?? 0);
    }

    /**
     * Convert points to a checkout discount, consuming them atomically. Redeems
     * in whole redemption units only. Returns the discount amount.
     *
     * @throws DomainConflictException when the balance can't cover a whole unit.
     */
    public function redeem(Customer $customer, int $points): float
    {
        $program = $this->program($customer->store);

        if ($program === null || ! $program->enabled) {
            throw new DomainConflictException('LOYALTY_NOT_ENABLED', 'This store does not have a loyalty program.');
        }

        $units = intdiv($points, $program->redeem_points);
        $spend = $units * $program->redeem_points;

        if ($units < 1) {
            throw new DomainConflictException('INSUFFICIENT_POINTS', 'Not enough points to redeem.');
        }

        return DB::transaction(function () use ($customer, $spend, $units, $program) {
            $account = LoyaltyAccount::where('customer_id', $customer->id)->lockForUpdate()->first();

            if ($account === null || $account->points < $spend) {
                throw new DomainConflictException('INSUFFICIENT_POINTS', 'Not enough points to redeem.');
            }

            $account->decrement('points', $spend);

            return (float) ($units * (float) $program->redeem_value);
        });
    }

    /** Earn points for a completed order's total. No-op without an active program. */
    public function accrueForOrder(Order $order): void
    {
        $program = $this->program($order->store);

        if ($program === null || ! $program->enabled || $order->customer_id === null) {
            return;
        }

        $points = (int) floor((float) $order->total * $program->earn_points_per_currency);

        if ($points > 0) {
            $account = LoyaltyAccount::firstOrCreate(
                ['store_id' => $order->store_id, 'customer_id' => $order->customer_id],
                ['points' => 0],
            );
            $account->increment('points', $points);
        }
    }
}
