<?php

namespace App\Support;

use App\Enums\Currency;
use App\Models\Order;

/**
 * Builds the pre-filled wa.me deep link for online orders — the "order summary message
 * sent to your WhatsApp Business number" described by the storefront WhatsApp toggle.
 */
class WhatsAppMessageBuilder
{
    public function url(Order $order): string
    {
        $number = preg_replace('/\D/', '', (string) $order->store->whatsapp);

        return "https://wa.me/{$number}?text=".rawurlencode($this->text($order));
    }

    public function text(Order $order): string
    {
        $symbol = Currency::tryFrom((string) $order->store->currency)?->symbol() ?? '';

        $lines = ["New order {$order->order_number}:", ''];
        foreach ($order->items as $item) {
            $name = $item->product_name.($item->variant_name ? " ({$item->variant_name})" : '');
            $lines[] = "{$item->quantity}x {$name} — {$symbol}{$item->line_total}";
        }
        $lines[] = '';
        $lines[] = "Subtotal: {$symbol}{$order->subtotal}";
        if ($order->delivery_fee_status === 'PENDING') {
            $lines[] = 'Delivery: TO BE QUOTED — please reply with the delivery fee.';
        } elseif ((float) $order->delivery_fee > 0) {
            $lines[] = $order->delivery_provider_name
                ? "Delivery ({$order->delivery_provider_name}, {$order->delivery_distance_km} km): {$symbol}{$order->delivery_fee}"
                : "Delivery: {$symbol}{$order->delivery_fee}";
        }
        if ((float) $order->tax_amount > 0) {
            $lines[] = "Tax: {$symbol}{$order->tax_amount}";
        }
        $lines[] = "Total: {$symbol}{$order->total}";
        $lines[] = '';
        $lines[] = 'Customer: '.$order->customer_name.' ('.$order->customer_phone.')';
        $lines[] = 'Fulfillment: '.strtolower((string) $order->fulfillment);

        // Both legs of the trip, so a courier knows where to collect and drop off.
        if ((string) $order->fulfillment === 'DELIVERY') {
            if ($order->pickup_latitude !== null && $order->pickup_longitude !== null) {
                $lines[] = 'Collect from: '.$order->pickup_address
                    ." — https://maps.google.com/?q={$order->pickup_latitude},{$order->pickup_longitude}";
            }
            if ($order->delivery_latitude !== null && $order->delivery_longitude !== null) {
                $lines[] = "Deliver to: https://maps.google.com/?q={$order->delivery_latitude},{$order->delivery_longitude}";
            }
        }

        return implode("\n", $lines);
    }
}
