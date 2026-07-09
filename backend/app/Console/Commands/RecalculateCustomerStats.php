<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;

/**
 * Rebuilds the cached customer counters (total_orders / total_spent) from completed
 * orders. A safety net if events are ever missed. See docs 02 (Page 6).
 */
class RecalculateCustomerStats extends Command
{
    protected $signature = 'customers:recalculate-stats';

    protected $description = 'Recompute customer total_orders and total_spent from completed orders';

    public function handle(): int
    {
        $count = 0;

        Customer::query()->withoutStoreScope()->chunkById(200, function ($customers) use (&$count) {
            foreach ($customers as $customer) {
                $completed = $customer->orders()->where('status', 'COMPLETE');
                $customer->forceFill([
                    'total_orders' => (clone $completed)->count(),
                    'total_spent' => (float) (clone $completed)->sum('total'),
                ])->saveQuietly();
                $count++;
            }
        });

        $this->info("Recalculated stats for {$count} customers.");

        return self::SUCCESS;
    }
}
