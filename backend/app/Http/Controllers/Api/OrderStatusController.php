<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;

class OrderStatusController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    public function update(UpdateOrderStatusRequest $request, int $order): OrderResource
    {
        $model = Order::with('items')->findOrFail($order);
        $updated = $this->orders->updateStatus($model, OrderStatus::from($request->validated('status')), $request->user());

        return new OrderResource($updated);
    }
}
