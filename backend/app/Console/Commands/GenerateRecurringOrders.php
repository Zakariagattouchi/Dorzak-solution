<?php

namespace App\Console\Commands;

use App\Services\RecurringOrderService;
use Illuminate\Console\Command;

class GenerateRecurringOrders extends Command
{
    protected $signature = 'recurring:generate';

    protected $description = 'Generate orders for every due recurring-order subscription';

    public function handle(RecurringOrderService $service): int
    {
        $count = $service->generateDue();

        $this->info("{$count} recurring order(s) generated.");

        return self::SUCCESS;
    }
}
