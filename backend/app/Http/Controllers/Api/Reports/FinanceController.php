<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Support\StoreContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceController extends Controller
{
    public function __construct(
        private readonly ReportService $reports,
        private readonly StoreContext $context,
    ) {}

    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('reports.view'), 403);

        $data = $this->reports->finance(
            $this->context->store(),
            $request->string('period', 'all')->toString(),
            $request->query('date_from'),
            $request->query('date_to'),
        );

        return response()->json(['data' => $data]);
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()->can('reports.export'), 403);

        $data = $this->reports->finance(
            $this->context->store(),
            $request->string('period', 'all')->toString(),
            $request->query('date_from'),
            $request->query('date_to'),
        );

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['order_number', 'customer', 'date', 'payment_method', 'tax', 'total']);
            foreach ($data['entries'] as $e) {
                fputcsv($out, [$e['order_number'], $e['customer_name'], $e['date'], $e['payment_method'], $e['tax_amount'], $e['total']]);
            }
            fclose($out);
        }, 'finance-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }
}
