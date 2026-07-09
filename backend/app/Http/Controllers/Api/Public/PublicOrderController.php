<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Concerns\ResolvesPublicStore;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StorePublicOrderRequest;
use App\Services\PublicOrderService;
use Illuminate\Http\JsonResponse;

class PublicOrderController extends Controller
{
    use ResolvesPublicStore;

    public function __invoke(StorePublicOrderRequest $request, string $slug, PublicOrderService $service): JsonResponse
    {
        $store = $this->resolvePublicStore($slug);
        $result = $service->place($store, $request->validated());
        $order = $result['order'];

        return response()->json([
            'data' => [
                'order_number' => $order->order_number,
                'status' => $order->status->value,
                'payment_status' => $order->payment_status->value,
                'fulfillment' => $order->fulfillment,
                'table_number' => $order->table_number,
                'subtotal' => $order->subtotal,
                'delivery_fee' => $order->delivery_fee,
                'tax_amount' => $order->tax_amount,
                'total' => $order->total,
                'whatsapp_url' => $result['whatsapp_url'],
            ],
        ], 201);
    }
}
