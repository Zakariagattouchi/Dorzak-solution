<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
class OrderResource extends JsonResource
{
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
            'total' => $this->total,
            'notes' => $this->notes,
            'placed_at' => $this->placed_at?->toIso8601String(),
            'placed_at_local' => $this->placed_at
                ?->clone()->setTimezone($this->store->timezone ?? 'UTC')->format('Y-m-d H:i'),
            'created_by' => $this->whenLoaded('creator', fn () => $this->creator ? [
                'id' => $this->creator->id, 'name' => $this->creator->name,
            ] : null),
            'receipt' => $this->when($this->withReceipt, fn () => $this->receiptBlock()),
        ];
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
