<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Maps raw `delivery_external_status` strings coming from the Dorzak Business
 * network into human-readable labels. Only network-delivery orders expose these
 * fields; all others get null so the UI can branch cleanly.
 */

/**
 * @mixin Order
 */
class OrderResource extends JsonResource
{
    /** @var array<string, string> */
    private const COURIER_LABELS = [
        'pending_dispatch'    => 'Finding a driver',
        'auctioning'          => 'Finding a driver',
        'accepted'            => 'Finding a driver',
        'en_route_pickup'     => 'Driver en route to you',
        'arrived_pickup'      => 'Driver en route to you',
        'en_route_customer'   => 'Out for delivery',
        'arrived_customer'    => 'Out for delivery',
        'out_for_delivery'    => 'Out for delivery',
        'delivered'           => 'Delivered',
        'failed'              => 'Delivery failed',
        'returned'            => 'Delivery returned',
        'cancelled'           => 'Delivery cancelled',
    ];
    /** Toggle to embed the receipt block (store header/footer) on the detail endpoint. */
    public bool $withReceipt = false;

    public function withReceipt(bool $value = true): static
    {
        $this->withReceipt = $value;

        return $this;
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status->value,
            'payment_method' => $this->payment_method->value,
            'payment_status' => $this->payment_status->value,
            'currency_code' => $this->currency_code,
            'source' => $this->source->value,
            'fulfillment' => $this->fulfillment,
            'table_number' => $this->table_number,
            'delivery_address' => $this->delivery_address,
            'delivery_address_details' => $this->delivery_address_details,
            'delivery_city' => $this->delivery_city,
            'delivery_latitude' => $this->delivery_latitude,
            'delivery_longitude' => $this->delivery_longitude,
            'payment_reference' => $this->payment_reference,
            'has_payment_proof' => $this->payment_proof_path !== null,
            'customer' => $this->whenLoaded('customer', fn () => $this->customer ? [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'phone' => $this->customer->phone,
            ] : null),
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'tax_rate' => $this->tax_rate,
            'tax_amount' => $this->tax_amount,
            'delivery_fee' => $this->delivery_fee,
            'delivery_fee_status' => $this->delivery_fee_status,
            'delivery_provider_name' => $this->delivery_provider_name,
            'delivery_distance_km' => $this->delivery_distance_km,
            // Where the courier collects (snapshotted at placement).
            'pickup_latitude' => $this->pickup_latitude,
            'pickup_longitude' => $this->pickup_longitude,
            'pickup_address' => $this->pickup_address,
            'total' => $this->total,
            'notes' => $this->notes,
            'placed_at' => $this->placed_at?->toIso8601String(),
            'placed_at_local' => $this->placed_at
                ?->clone()->setTimezone($this->store->timezone ?? 'UTC')->format('Y-m-d H:i'),
            'created_by' => $this->whenLoaded('creator', fn () => $this->creator ? [
                'id' => $this->creator->id, 'name' => $this->creator->name,
            ] : null),
            // Network-delivery courier state — only present for dorzak-dispatched orders.
            // Internal tender IDs and auction internals are never exposed here.
            'courier_state' => $this->courierState(),
            'delivery_dispatched_at' => $this->delivery_provider_code === 'dorzak'
                ? $this->delivery_dispatched_at?->toIso8601String()
                : null,
            'delivery_external_reference' => $this->delivery_provider_code === 'dorzak'
                ? $this->delivery_external_reference
                : null,
            'receipt' => $this->when($this->withReceipt, fn () => $this->receiptBlock()),
        ];
    }

    /**
     * A human-readable courier label derived from the raw external status string.
     * Returns null for non-network orders (no delivery_provider_code = 'dorzak').
     */
    private function courierState(): ?string
    {
        if ($this->delivery_provider_code !== 'dorzak') {
            return null;
        }

        $raw = (string) ($this->delivery_external_status ?? '');

        if ($raw === '') {
            return null;
        }

        return self::COURIER_LABELS[$raw] ?? ucwords(str_replace('_', ' ', $raw));
    }

    /** @return array<string, mixed> */
    private function receiptBlock(): array
    {
        $store = $this->store;
        $receipt = $store->receiptSetting;

        return [
            'business_name' => $store->name,
            'owner_name' => $store->owner_name,
            'phone' => $store->phone,
            'address' => $store->address,
            'header' => $receipt?->header,
            'footer' => $receipt?->footer,
            'show_logo' => (bool) $receipt?->show_logo,
            'show_address' => (bool) $receipt?->show_address,
            'show_tax' => (bool) $receipt?->show_tax,
        ];
    }
}
