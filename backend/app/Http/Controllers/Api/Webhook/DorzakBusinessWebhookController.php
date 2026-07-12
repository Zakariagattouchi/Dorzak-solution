<?php

namespace App\Http\Controllers\Api\Webhook;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\DorzakBusinessClient;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Driver-side status flowing back from the Dorzak delivery board.
 *
 * Authenticated by the same exact-body HMAC the outbound calls use — signed
 * over "{timestamp}.{event_type}.{source_order_id}.{raw_body}" — plus a 5-minute
 * replay window. Unsigned or stale calls are rejected.
 *
 * Completion side-effects (stock deduction, loyalty, referral, OrderCompleted
 * event) are always routed through OrderService::complete(), so a webhook-
 * completed order is indistinguishable from a merchant-completed one.
 *
 * Idempotency: once an order is COMPLETE it will not be re-completed, and status
 * is never regressed (e.g. a stale en_route_customer after delivered is ignored).
 */
class DorzakBusinessWebhookController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    public function __invoke(Request $request): JsonResponse
    {
        $eventType = (string) $request->header('X-Dorzak-Event-Type');
        $timestamp = (string) $request->header('X-Dorzak-Timestamp');
        $signature = (string) $request->header('X-Dorzak-Signature');
        $payload = $request->json()->all();
        $sourceOrderId = (string) ($payload['source_order_id'] ?? '');
        $secret = (string) config('delivery.business.webhook_secret');

        if ($eventType === '' || $sourceOrderId === '' || $secret === ''
            || ! ctype_digit($timestamp)
            || abs(now()->timestamp - (int) $timestamp) > 300) {
            return response()->json(['ok' => false, 'message' => 'Invalid webhook metadata.'], 401);
        }

        $expected = DorzakBusinessClient::sign($secret, $eventType, $sourceOrderId, (string) $request->getContent(), (int) $timestamp);

        if (! hash_equals($expected, $signature)) {
            return response()->json(['ok' => false, 'message' => 'Invalid webhook signature.'], 401);
        }

        $order = Order::withoutGlobalScopes()->find((int) $sourceOrderId);

        if ($order === null) {
            return response()->json(['ok' => true, 'ignored' => true]);
        }

        DB::transaction(function () use ($order, $payload, $eventType): void {
            // Lock the row so concurrent webhooks don't race on status.
            $order = Order::withoutGlobalScopes()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $order->load('items');

            // Always record the raw delivery carrier state and the external ref.
            $meta = ['delivery_external_status' => (string) ($payload['to'] ?? $eventType)];
            if (isset($payload['tender_id'])) {
                $meta['delivery_external_reference'] = (string) $payload['tender_id'];
            }
            $order->forceFill($meta)->save();

            $state = $payload['to'] ?? null;

            // Map the driver's journey onto the merchant's order status.
            // Never regress: skip transitions to earlier states.
            if (in_array($state, ['en_route_customer', 'arrived_customer'], true)) {
                if ($order->status !== OrderStatus::CANCELLED
                    && $order->status !== OrderStatus::COMPLETE
                    && $order->status !== OrderStatus::OUT_FOR_DELIVERY
                ) {
                    $order->forceFill(['status' => OrderStatus::OUT_FOR_DELIVERY])->save();
                }
            } elseif ($state === 'delivered' || $eventType === 'delivery.completed') {
                // Idempotent: a second delivery.completed must not re-run side-effects.
                if ($order->status !== OrderStatus::CANCELLED
                    && $order->status !== OrderStatus::COMPLETE
                ) {
                    // Route through the canonical completion path: deducts stock,
                    // applies loyalty/referral, sets completed_at, fires OrderCompleted.
                    $this->orders->complete($order);
                }
            }
        });

        return response()->json(['ok' => true]);
    }
}
