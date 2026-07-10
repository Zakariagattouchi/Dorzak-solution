<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\DorzakBusinessClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/** Withdraws a delivery request from the driver board when the order is cancelled. */
class CancelDorzakDelivery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public array $backoff = [10, 30, 60, 300, 900];

    public function __construct(public readonly int $orderId) {}

    public function handle(DorzakBusinessClient $business): void
    {
        $order = Order::withoutGlobalScopes()->find($this->orderId);

        if ($order === null || ! $business->enabled() || blank($order->delivery_external_reference)) {
            return;
        }

        $business->cancelDelivery($order);

        $order->forceFill(['delivery_external_status' => 'cancelled'])->save();
    }
}
