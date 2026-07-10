<?php

namespace App\Jobs;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\DorzakBusinessClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Opens the driver auction on the Dorzak delivery board. Fired when a merchant
 * accepts an order (status -> PREPARING): we never summon a driver for an order
 * the merchant might reject.
 *
 * Idempotent on the delivery side (Idempotency-Key) and guarded here by
 * delivery_external_reference, so retries can't double-book a driver.
 */
class DispatchDorzakDelivery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 8;

    public int $timeout = 30;

    public array $backoff = [10, 30, 60, 120, 300, 600, 1200];

    public function __construct(public readonly int $orderId) {}

    public function handle(DorzakBusinessClient $business): void
    {
        $order = Order::withoutGlobalScopes()->find($this->orderId);

        if ($order === null || ! $business->enabled()) {
            return;
        }

        // Only the Dorzak network carrier is dispatchable, and only once.
        if ($order->delivery_provider_code !== 'dorzak'
            || $order->delivery_quote_id === null
            || filled($order->delivery_external_reference)) {
            return;
        }

        if ($order->status !== OrderStatus::PREPARING) {
            return;
        }

        // Cash-on-delivery tells the driver how much to collect.
        $collectsCash = $order->payment_method === PaymentMethod::CASH
            && $order->payment_status !== PaymentStatus::PAID;

        $response = $business->createDelivery(
            $order,
            $collectsCash ? (int) round((float) $order->total * 100) : null,
        );

        $order->forceFill([
            'delivery_external_reference' => (string) ($response['tender_id'] ?? ''),
            'delivery_external_status' => (string) ($response['status'] ?? ''),
            'delivery_dispatched_at' => now(),
        ])->save();
    }
}
