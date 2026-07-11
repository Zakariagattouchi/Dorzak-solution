<?php

namespace App\Http\Controllers\Api;

use App\Enums\PlanFeature;
use App\Http\Controllers\Controller;
use App\Models\CustomerSegment;
use App\Services\PlanGate;
use App\Services\SegmentService;
use App\Support\StoreContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Saved customer segments (premium — PlanFeature::SEGMENTS). */
class SegmentController extends Controller
{
    public function __construct(
        private readonly PlanGate $plans,
        private readonly StoreContext $context,
        private readonly SegmentService $segments,
    ) {}

    public function index(): JsonResponse
    {
        $rows = CustomerSegment::orderByDesc('id')->get()->map(fn (CustomerSegment $s) => [
            'id' => $s->id, 'name' => $s->name, 'rules' => $s->rules, 'count' => $this->segments->count($s),
        ]);

        return response()->json(['segments' => $rows]);
    }

    public function store(Request $request): JsonResponse
    {
        $store = $this->context->store();
        $this->plans->ensure($store, PlanFeature::SEGMENTS);
        abort_unless($request->user()->can('settings.manage'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'rules' => ['required', 'array'],
            'rules.min_orders' => ['nullable', 'integer', 'min:0'],
            'rules.max_orders' => ['nullable', 'integer', 'min:0'],
            'rules.min_spent' => ['nullable', 'numeric', 'min:0'],
            'rules.max_spent' => ['nullable', 'numeric', 'min:0'],
        ]);

        $segment = $this->segments->create($store, $data);

        return response()->json(['id' => $segment->id], 201);
    }

    public function destroy(Request $request, CustomerSegment $segment): JsonResponse
    {
        abort_unless($segment->store_id === $this->context->store()->id, 404);
        abort_unless($request->user()->can('settings.manage'), 403);

        $segment->delete();

        return response()->json(['ok' => true]);
    }
}
