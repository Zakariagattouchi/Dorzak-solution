<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\UpdatePaymentStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;

class OrderPaymentStatusController extends Controller
{
    public function update(UpdatePaymentStatusRequest $request, int $order): OrderResource
    {
        $model = Order::with(['items', 'customer', 'creator'])->findOrFail($order);
        $model->update(['payment_status' => PaymentStatus::from($request->validated('payment_status'))]);

        return new OrderResource($model->fresh(['items', 'customer', 'creator']));
    }
}
