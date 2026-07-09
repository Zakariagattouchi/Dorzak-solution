<?php

namespace App\Services;

use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Events\OnlineOrderPlaced;
use App\Exceptions\DomainConflictException;
use App\Models\Order;
use App\Models\Store;
use App\Notifications\OnlineOrderNotification;
use App\Support\StoreContext;
use App\Support\WhatsAppMessageBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

/**
 * Anonymous online-order flow (WhatsApp checkout). Enforces fulfillment rules,
 * delivery-fee + free-delivery threshold, minimum order, and item availability,
 * upserts the customer by phone, then delegates order creation to OrderService.
 * See docs 02 (Page 11).
 */
class PublicOrderService
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly WhatsAppMessageBuilder $whatsapp,
        private readonly StoreContext $context,
    ) {}

    /**
     * @return array{order: Order, whatsapp_url: string}
     */
    public function place(Store $store, array $data): array
    {
        // Scope subsequent catalog queries to this store (no authenticated membership).
        $this->context->setStore($store);
        $sf = $store->storefrontSetting;
        $fulfillment = strtoupper($data['fulfillment']);

        if ($fulfillment === 'DELIVERY' && ! $sf->allow_delivery) {
            throw ValidationException::withMessages(['fulfillment' => ['Delivery is not available for this store.']]);
        }
        if ($fulfillment === 'PICKUP' && ! $sf->allow_pickup) {
            throw ValidationException::withMessages(['fulfillment' => ['Pickup is not available for this store.']]);
        }
        if ($fulfillment === 'DINE_IN' && ! $sf->allow_dine_in) {
            throw ValidationException::withMessages(['fulfillment' => ['Dine-in ordering is not available for this store.']]);
        }

        [$subtotal, $unavailable] = $this->priceItems($store, $data['items']);

        if ($unavailable !== []) {
            throw new DomainConflictException('ITEM_UNAVAILABLE', 'Some items are no longer available.', $unavailable);
        }

        if ($subtotal < (float) $sf->min_order_amount) {
            throw new DomainConflictException('MIN_ORDER_NOT_MET', "Minimum order is {$sf->min_order_amount}.", [
                'min_order_amount' => (float) $sf->min_order_amount, 'subtotal' => $subtotal,
            ]);
        }

        $deliveryFee = $this->deliveryFee($sf, $fulfillment, $subtotal);

        return DB::transaction(function () use ($store, $data, $fulfillment, $deliveryFee): array {
            $customer = $this->upsertCustomer($store, $data['customer']);

            $order = $this->orders->create($store, [
                'items' => $data['items'],
                'customer_id' => $customer->id,
                'payment_method' => $data['payment_method'] ?? ($fulfillment === 'DINE_IN' ? PaymentMethod::CASH->value : PaymentMethod::WHATSAPP->value),
                'payment_status' => ($data['payment_method'] ?? PaymentMethod::WHATSAPP->value) === PaymentMethod::FAWRAN->value
                    ? PaymentStatus::PENDING_VERIFICATION->value
                    : PaymentStatus::UNPAID->value,
                'status' => OrderStatus::CONFIRMING->value,
                'source' => OrderSource::ONLINE->value,
                'fulfillment' => $fulfillment,
                'table_number' => $fulfillment === 'DINE_IN' ? ($data['table_number'] ?? null) : null,
                'delivery_fee' => $deliveryFee,
                'notes' => $data['notes'] ?? null,
                'delivery_address' => $data['customer']['address'] ?? null,
                'delivery_address_details' => $data['customer']['address_details'] ?? null,
                'delivery_city' => $data['customer']['city'] ?? null,
                'delivery_latitude' => $data['customer']['latitude'] ?? null,
                'delivery_longitude' => $data['customer']['longitude'] ?? null,
                'payment_reference' => $data['payment_reference'] ?? null,
                'payment_proof_path' => isset($data['payment_proof'])
                    ? $data['payment_proof']->store("payment-proofs/{$store->id}", 'local')
                    : null,
            ]);

            $order->load(['items', 'store']);

            if ($order->payment_method === PaymentMethod::FAWRAN && blank($order->payment_reference)) {
                $order->update(['payment_reference' => $customer->name]);
                $order->refresh();
            }

            $url = $order->store->whatsapp ? $this->whatsapp->url($order) : null;

            $this->notifyStaff($store, $order);
            OnlineOrderPlaced::dispatch($order);

            return ['order' => $order, 'whatsapp_url' => $url];
        });
    }

    /**
     * @return array{0: float, 1: list<array{product_id:int}>}
     */
    private function priceItems(Store $store, array $items): array
    {
        $subtotal = 0.0;
        $unavailable = [];

        foreach ($items as $item) {
            $product = $store->products()->where('is_active', true)
                ->where('show_in_online_store', true)->find($item['product_id']);

            if ($product === null) {
                $unavailable[] = ['product_id' => (int) $item['product_id']];

                continue;
            }

            $price = (float) $product->effectivePrice();
            $inStock = ! $product->track_stock || $product->stock > 0;

            if (! empty($item['variant_id'])) {
                $variant = $product->variants()->find($item['variant_id']);
                if ($variant === null) {
                    $unavailable[] = ['product_id' => $product->id];

                    continue;
                }
                $price = (float) $variant->price;
                $inStock = $variant->stock > 0;
            }

            if (! $inStock) {
                $unavailable[] = ['product_id' => $product->id];

                continue;
            }

            $subtotal += round($price * (int) $item['quantity'], 2);
        }

        return [round($subtotal, 2), $unavailable];
    }

    private function deliveryFee($sf, string $fulfillment, float $subtotal): float
    {
        if ($fulfillment !== 'DELIVERY') {
            return 0.0;
        }

        $threshold = $sf->free_delivery_threshold;
        if ($threshold !== null && $subtotal >= (float) $threshold) {
            return 0.0;
        }

        return (float) $sf->delivery_fee;
    }

    private function upsertCustomer(Store $store, array $data)
    {
        $normalized = preg_replace('/\D/', '', (string) $data['phone']);

        return $store->customers()->where('phone_normalized', $normalized)->first()
            ?? $store->customers()->create([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'address' => $data['address'] ?? null,
                'address_details' => $data['address_details'] ?? null,
                'city' => $data['city'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
            ]);
    }

    private function notifyStaff(Store $store, Order $order): void
    {
        $recipients = $store->users()
            ->wherePivotIn('role', ['OWNER', 'MANAGER'])
            ->wherePivot('is_active', true)
            ->get();

        Notification::send($recipients, new OnlineOrderNotification($order));
    }
}
