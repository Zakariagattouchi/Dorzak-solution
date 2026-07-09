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
        if ((float) $order->delivery_fee > 0) {
            $lines[] = "Delivery: {$symbol}{$order->delivery_fee}";
        }
        if ((float) $order->tax_amount > 0) {
            $lines[] = "Tax: {$symbol}{$order->tax_amount}";
        }
        $lines[] = "Total: {$symbol}{$order->total}";
        $lines[] = '';
        $lines[] = 'Customer: '.$order->customer_name.' ('.$order->customer_phone.')';
        $lines[] = 'Fulfillment: '.strtolower((string) $order->fulfillment);

        return implode("\n", $lines);
    }
}
