<?php

namespace App\Jobs;

use App\Models\Store;
use App\Services\CustomerImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Processes a large customer CSV import off the request cycle (> 500 rows).
 */
class ImportCustomersJob implements ShouldQueue
{
    use Queueable;

    /** @param list<array<string, string|null>> $rows */
    public function __construct(
        public int $storeId,
        public array $rows,
    ) {}

    public function handle(CustomerImportService $service): void
    {
        $store = Store::findOrFail($this->storeId);
        $service->import($store, $this->rows);
    }
}
