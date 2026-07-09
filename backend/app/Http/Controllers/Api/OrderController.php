<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Support\StoreContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly StoreContext $context,
    ) {}

    /** GET /orders — filters + a summary over the filtered set. */
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('orders.view'), 403);

        $base = $this->filtered($request);

        $perPage = min(max($request->integer('per_page', 25), 1), 200);
        $payload = OrderResource::collection(
            (clone $base)->with(['items', 'customer', 'creator'])->orderByDesc('placed_at')->paginate($perPage)->withQueryString()
        )->response()->getData(true);

        $summary = (clone $base);
        $payload['meta']['summary'] = [
            'revenue' => number_format((float) (clone $summary)->where('status', '!=', 'CANCELLED')->sum('total'), 2, '.', ''),
            'completed_count' => (clone $summary)->where('status', 'COMPLETE')->count(),
            'pending_count' => (clone $summary)->whereNotIn('status', ['COMPLETE', 'CANCELLED'])->count(),
            'cancelled_count' => (clone $summary)->where('status', 'CANCELLED')->count(),
            'tax_total' => number_format((float) (clone $summary)->where('status', '!=', 'CANCELLED')->sum('tax_amount'), 2, '.', ''),
            'discount_total' => number_format((float) (clone $summary)->where('status', '!=', 'CANCELLED')->sum('discount'), 2, '.', ''),
        ];

        return response()->json($payload);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orders->create($this->context->store(), $request->validated(), $request->user());

        return (new OrderResource($order))->response()->setStatusCode(201);
    }

    public function show(int $order): OrderResource
    {
        $model = Order::with(['items', 'customer', 'creator'])->findOrFail($order);

        return (new OrderResource($model))->withReceipt();
    }

    /** GET /orders/export — CSV of the filtered set. */
    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()->can('reports.export'), 403);

        $query = $this->filtered($request)->orderByDesc('placed_at');

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['order_number', 'date', 'customer', 'phone', 'payment_method', 'status', 'subtotal', 'discount', 'tax', 'total', 'items_count']);
            $query->with('items')->chunk(500, function ($orders) use ($out) {
                foreach ($orders as $o) {
                    fputcsv($out, [
                        $o->order_number, $o->placed_at?->format('Y-m-d H:i'), $o->customer_name, $o->customer_phone,
                        $o->payment_method->value, $o->status->value, $o->subtotal, $o->discount, $o->tax_amount,
                        $o->total, $o->items->count(),
                    ]);
                }
            });
            fclose($out);
        }, 'orders-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    /** Build the base filtered query shared by index + export. */
    private function filtered(Request $request)
    {
        $query = Order::query();

        if ($search = $request->string('search')->toString()) {
            $like = '%'.$search.'%';
            $query->where(fn ($q) => $q->where('order_number', 'like', $like)->orWhere('customer_name', 'like', $like));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->upper());
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->string('payment_method')->upper());
        }
        if ($request->filled('source')) {
            $query->where('source', $request->string('source')->upper());
        }
        if ($request->filled('date_from')) {
            $query->whereDate('placed_at', '>=', $request->date('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('placed_at', '<=', $request->date('date_to'));
        }

        return $query;
    }
}
