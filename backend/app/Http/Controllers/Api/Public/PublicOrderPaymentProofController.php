<?php

namespace App\Http\Controllers\Api\Public;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Concerns\ResolvesPublicStore;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * POST /api/public/stores/{slug}/orders/{orderNumber}/payment-proof
 *
 * Closes the WhatsApp manual-quote loop: once the merchant has SET the
 * delivery fee, the customer pays by Fawran transfer from the public order
 * status page and uploads the receipt here. Blocked while the fee is still
 * PENDING (the total is unknown) and once the order is paid or cancelled.
 */
class PublicOrderPaymentProofController extends Controller
{
    use ResolvesPublicStore;

    public function __invoke(Request $request, string $slug, string $orderNumber): JsonResponse
    {
        $data = $request->validate([
            'payment_proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'payment_reference' => ['nullable', 'string', 'max:120'],
        ]);

        $store = $this->resolvePublicStore($slug);
        $order = $store->orders()->where('order_number', $orderNumber)->first();

        abort_if($order === null, 404, 'Order not found.');
        abort_unless((bool) $store->storefrontSetting?->fawran_enabled, 422, 'Transfers are not enabled for this store.');
        abort_if($order->status === OrderStatus::CANCELLED, 422, 'This order was cancelled.');
        abort_if($order->payment_status !== PaymentStatus::UNPAID, 422, 'This order is already being processed.');
        abort_if($order->delivery_fee_status === 'PENDING', 422, 'The delivery fee has not been confirmed yet.');

        $order->update([
            'payment_method' => PaymentMethod::FAWRAN,
            'payment_status' => PaymentStatus::PENDING_VERIFICATION,
            'payment_reference' => $data['payment_reference'] ?? $order->customer_name,
            'payment_proof_path' => $data['payment_proof']->store("payment-proofs/{$store->id}", 'local'),
        ]);

        return response()->json([
            'data' => [
                'order_number' => $order->order_number,
                'payment_status' => $order->fresh()->payment_status->value,
            ],
        ]);
    }
}
