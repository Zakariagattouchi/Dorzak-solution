<?php

namespace App\Http\Controllers\Api\Webhook;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\DorzakBusinessClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Driver-side status flowing back from the Dorzak delivery board.
 *
 * Authenticated by the same exact-body HMAC the outbound calls use — signed
 * over "{timestamp}.{event_type}.{source_order_id}.{raw_body}" — plus a 5-minute
 * replay window. Unsigned or stale calls are rejected.
 */
class DorzakBusinessWebhookController extends Controller
{
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

        $values = ['delivery_external_status' => (string) ($payload['to'] ?? $eventType)];

        if (isset($payload['tender_id'])) {
            $values['delivery_external_reference'] = (string) $payload['tender_id'];
        }

        // Map the driver's journey onto the merchant's order status. COMPLETE is
        // only reached from OUT_FOR_DELIVERY, matching the normal transitions.
        $state = $payload['to'] ?? null;

        if (in_array($state, ['en_route_customer', 'arrived_customer'], true)) {
            if ($order->status !== OrderStatus::CANCELLED && $order->status !== OrderStatus::COMPLETE) {
                $values['status'] = OrderStatus::OUT_FOR_DELIVERY;
            }
        } elseif ($state === 'delivered' || $eventType === 'delivery.completed') {
            if ($order->status !== OrderStatus::CANCELLED) {
                $values['status'] = OrderStatus::COMPLETE;
                $values['completed_at'] = now();
            }
        }

        $order->forceFill($values)->save();

        return response()->json(['ok' => true]);
    }
}
