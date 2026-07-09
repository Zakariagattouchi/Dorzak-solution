<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Concerns\ResolvesPublicStore;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PublicOrderShowController extends Controller
{
    use ResolvesPublicStore;

    public function __invoke(string $slug, string $orderNumber): JsonResponse|Response
    {
        $store = $this->resolvePublicStore($slug);

        /** @var Order|null $order */
        $order = $store->orders()
            ->where('order_number', $orderNumber)
            ->with(['items'])
            ->first();

        if ($order === null) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status->value,
                'payment_status' => $order->payment_status->value,
                'payment_method' => $order->payment_method->value,
                'fulfillment' => $order->fulfillment,
                'table_number' => $order->table_number,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'delivery_address' => $order->delivery_address,
                'delivery_address_details' => $order->delivery_address_details,
                'delivery_city' => $order->delivery_city,
                'subtotal' => $order->subtotal,
                'delivery_fee' => $order->delivery_fee,
                'delivery_fee_status' => $order->delivery_fee_status,
                'delivery_provider_name' => $order->delivery_provider_name,
                'delivery_distance_km' => $order->delivery_distance_km,
                'tax_amount' => $order->tax_amount,
                'discount' => $order->discount,
                'total' => $order->total,
                'currency_code' => $order->currency_code,
                'notes' => $order->notes,
                'placed_at' => $order->placed_at?->toDateTimeString(),
                'placed_at_local' => $order->placed_at?->format('Y-m-d H:i'),
                'items' => $order->items->map(fn ($item) => [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'variant_id' => $item->variant_id,
                    'variant_name' => $item->variant_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                ]),
            ],
        ]);
    }
}
