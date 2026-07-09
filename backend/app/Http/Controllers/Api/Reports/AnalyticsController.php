<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Support\StoreContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(
        private readonly ReportService $reports,
        private readonly StoreContext $context,
    ) {}

    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('reports.view'), 403);

        $data = $this->reports->analytics(
            $this->context->store(),
            $request->string('period', 'all')->toString(),
        );

        return response()->json(['data' => $data]);
    }
}
