<?php

namespace App\Services;

use App\Enums\PlanFeature;
use App\Models\OrderSubscription;
use App\Models\Store;

/**
 * Recurring customer orders (premium — PlanFeature::RECURRING_ORDERS). Each due
 * subscription generates a normal order through OrderService using the store's
 * existing payment types (default: a pending order the merchant/customer settles
 * as usual — no saved-card vault), then advances to the next cycle.
 */
class RecurringOrderService
{
    public function __construct(
        private readonly PlanGate $plans,
        private readonly OrderService $orders,
    ) {}

    /** @param array<string, mixed> $data */
    public function create(Store $store, array $data): OrderSubscription
    {
        $this->plans->ensure($store, PlanFeature::RECURRING_ORDERS);

        return OrderSubscription::create([
            'store_id' => $store->id,
            'customer_id' => $data['customer_id'],
            'items' => $data['items'],
            'cadence' => ($data['cadence'] ?? 'WEEKLY') === 'MONTHLY' ? 'MONTHLY' : 'WEEKLY',
            'status' => 'active',
            'next_run_at' => $data['next_run_at'] ?? now(),
        ]);
    }

    /** Generate an order for every active subscription that is due. Returns the count. */
    public function generateDue(): int
    {
        $due = OrderSubscription::query()
            ->where('status', 'active')
            ->where('next_run_at', '<=', now())
            ->get();

        foreach ($due as $subscription) {
            $this->run($subscription);
        }

        return $due->count();
    }

    private function run(OrderSubscription $subscription): void
    {
        $this->orders->create($subscription->store, [
            'items' => $subscription->items,
            'customer_id' => $subscription->customer_id,
            'payment_method' => 'CASH',
            'source' => 'ONLINE',
            'status' => 'CONFIRMING', // enters the normal fulfilment flow, unpaid
        ]);

        $subscription->update([
            'last_run_at' => now(),
            'next_run_at' => $subscription->cadence === 'MONTHLY'
                ? $subscription->next_run_at->addMonthNoOverflow()
                : $subscription->next_run_at->addWeek(),
        ]);
    }
}
