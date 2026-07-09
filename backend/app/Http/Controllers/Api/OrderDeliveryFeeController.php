<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\UpdateOrderDeliveryFeeRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;

/**
 * PATCH /orders/{order}/delivery-fee — the merchant side of the WhatsApp
 * manual-quote loop: sets (or corrects) the delivery fee on an unpaid
 * delivery order and recomputes the total.
 */
class OrderDeliveryFeeController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    public function update(UpdateOrderDeliveryFeeRequest $request, int $order): OrderResource
    {
        // Resolved here so the tenant scope is active (cross-store id -> 404).
        $model = Order::with('items')->findOrFail($order);

        $updated = $this->orders->setDeliveryFee($model, (float) $request->validated('delivery_fee'), $request->user());

        return new OrderResource($updated);
    }
}
