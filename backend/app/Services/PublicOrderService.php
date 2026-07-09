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
        private readonly DeliveryQuoteService $quotes,
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

        $delivery = $this->resolveDelivery($store, $data, $fulfillment, $subtotal);

        return DB::transaction(function () use ($store, $data, $fulfillment, $delivery): array {
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
                'delivery_fee' => $delivery['fee'],
                'delivery_provider_id' => $delivery['provider_id'],
                'delivery_provider_name' => $delivery['provider_name'],
                'delivery_distance_km' => $delivery['distance_km'],
                'delivery_fee_status' => $delivery['status'],
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

    /**
     * Server-side delivery pricing — the client's numbers are never trusted.
     *
     * @return array{fee:float, provider_id:?int, provider_name:?string, distance_km:?float, status:?string}
     */
    private function resolveDelivery(Store $store, array $data, string $fulfillment, float $subtotal): array
    {
        $none = ['fee' => 0.0, 'provider_id' => null, 'provider_name' => null, 'distance_km' => null, 'status' => null];

        if ($fulfillment !== 'DELIVERY') {
            return $none;
        }

        $lat = $data['customer']['latitude'] ?? null;
        $lng = $data['customer']['longitude'] ?? null;

        if ($lat === null || $lng === null) {
            throw ValidationException::withMessages([
                'customer.latitude' => ['Drop a pin so we can price your delivery.'],
            ]);
        }

        $quote = $this->quotes->quote($store, (float) $lat, (float) $lng, $subtotal);

        if ($quote['mode'] === 'unavailable') {
            throw new DomainConflictException('DELIVERY_UNAVAILABLE', 'Delivery is not available to this location.', [
                'reason' => $quote['reason'],
            ]);
        }

        if ($quote['mode'] === 'whatsapp_pending') {
            // Total is unknown until the merchant quotes the trip — can't pay by transfer yet.
            if (($data['payment_method'] ?? null) === PaymentMethod::FAWRAN->value) {
                throw ValidationException::withMessages([
                    'payment_method' => ['The delivery fee must be confirmed before paying by transfer.'],
                ]);
            }

            return ['fee' => 0.0, 'provider_id' => null, 'provider_name' => null, 'distance_km' => null, 'status' => 'PENDING'];
        }

        return [
            'fee' => (float) $quote['fee'],
            'provider_id' => $quote['provider_id'],
            'provider_name' => $quote['provider_name'],
            'distance_km' => $quote['distance_km'],
            // 'flat' keeps the legacy null shape; provider quotes are marked QUOTED.
            'status' => $quote['mode'] === 'quoted' ? 'QUOTED' : null,
        ];
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
