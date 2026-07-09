<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\ImportCustomersRequest;
use App\Jobs\ImportCustomersJob;
use App\Services\CustomerImportService;
use App\Support\StoreContext;
use Illuminate\Http\JsonResponse;

class CustomerImportController extends Controller
{
    private const ASYNC_THRESHOLD = 500;

    public function __invoke(ImportCustomersRequest $request, StoreContext $context, CustomerImportService $service): JsonResponse
    {
        $rows = $this->parse($request->file('file')->getRealPath());
        $store = $context->store();

        if (count($rows) > self::ASYNC_THRESHOLD) {
            ImportCustomersJob::dispatch($store->id, $rows);

            return response()->json([
                'message' => 'Your import is being processed. We\'ll notify you when it\'s done.',
                'data' => ['queued' => true, 'rows' => count($rows)],
            ], 202);
        }

        return response()->json(['data' => $service->import($store, $rows)]);
    }

    /**
     * Parse a CSV into assoc rows keyed by header. Header is normalized to lower snake.
     *
     * @return list<array<string, string|null>>
     */
    private function parse(string $path): array
    {
        $handle = fopen($path, 'r');
        $header = null;
        $rows = [];

        while (($cols = fgetcsv($handle)) !== false) {
            if ($cols === [null] || $cols === false) {
                continue;
            }
            if ($header === null) {
                $header = array_map(fn ($h) => strtolower(trim((string) $h)), $cols);

                continue;
            }
            $rows[] = collect($header)->mapWithKeys(fn ($key, $i) => [$key => $cols[$i] ?? null])->all();
        }

        fclose($handle);

        return $rows;
    }
}
