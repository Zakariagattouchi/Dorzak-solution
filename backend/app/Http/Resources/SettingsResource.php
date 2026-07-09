<?php

namespace App\Http\Resources;

use App\Enums\Currency;
use App\Models\Store;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The grouped settings envelope consumed by the Settings + Storefront pages.
 * Shape mirrors docs 02 (Page 12 / Page 10). Media columns hold either a stored
 * path or an absolute URL; both resolve to a URL here.
 *
 * @mixin Store
 */
class SettingsResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $store = $this->resource;
        $storefront = $store->storefrontSetting;
        $receipt = $store->receiptSetting;
        $integration = $store->integrationSetting;
        $methods = $store->accepted_payment_methods ?? [];

        $currency = Currency::tryFrom((string) $store->currency);

        return [
            'general' => [
                'business_name' => $store->name,
                'tagline' => $store->tagline,
                'phone' => $store->phone,
                'whatsapp' => $store->whatsapp,
                'language' => $store->language,
            ],
            'business' => [
                'owner_name' => $store->owner_name,
                'email' => $store->email,
                'address' => $store->address,
                'city' => $store->city,
                'state' => $store->state,
                'zip_code' => $store->zip_code,
                'country' => $store->country,
                'latitude' => $store->latitude !== null ? (float) $store->latitude : null,
                'longitude' => $store->longitude !== null ? (float) $store->longitude : null,
            ],
            'currency' => [
                'currency' => $store->currency,
                'currency_symbol' => $currency?->symbol(),
                'symbol_placement' => $store->symbol_placement,
            ],
            'taxes' => [
                'charge_sales_tax' => (bool) $store->charge_sales_tax,
                'tax_rate' => $store->tax_rate,
                'tax_id' => $store->tax_id,
                'tax_included_in_price' => (bool) $store->tax_included_in_price,
            ],
            'payments' => [
                'cash' => in_array('CASH', $methods, true),
                'card' => in_array('CARD', $methods, true),
                'transfer' => in_array('TRANSFER', $methods, true),
                'whatsapp' => in_array('WHATSAPP', $methods, true),
                'bank_name' => $store->payment_details['bank_name'] ?? '',
                'bank_account_name' => $store->payment_details['bank_account_name'] ?? '',
                'bank_account_number' => $store->payment_details['bank_account_number'] ?? '',
                'bank_iban' => $store->payment_details['bank_iban'] ?? '',
                'whatsapp_phone' => $store->payment_details['whatsapp_phone'] ?? '',
            ],
            'receipts' => [
                'header' => $receipt?->header,
                'footer' => $receipt?->footer,
                'show_logo' => (bool) $receipt?->show_logo,
                'show_address' => (bool) $receipt?->show_address,
                'show_tax' => (bool) $receipt?->show_tax,
                'auto_print' => (bool) $receipt?->auto_print,
            ],
            'integrations' => [
                'facebook_pixel_id' => $integration?->facebook_pixel_id,
                'google_analytics_id' => $integration?->google_analytics_id,
                'facebook_connected' => (bool) $integration?->facebook_connected,
                'facebook_page_name' => $integration?->facebook_page_name,
            ],
            'storefront' => [
                'online_store_enabled' => (bool) $storefront?->online_store_enabled,
                'slug' => $storefront?->slug,
                'public_url' => $storefront?->publicUrl(),
                'bio' => $storefront?->bio,
                'banner_url' => $this->mediaUrl($storefront?->banner_path),
                'logo_url' => $this->mediaUrl($storefront?->logo_path),
                'accent_color' => $storefront?->accent_color,
                'secondary_color' => $storefront?->secondary_color,
                'allow_delivery' => (bool) $storefront?->allow_delivery,
                'allow_pickup' => (bool) $storefront?->allow_pickup,
                'allow_dine_in' => (bool) $storefront?->allow_dine_in,
                'dine_in_table_count' => (int) ($storefront?->dine_in_table_count ?? 0),
                'delivery_fee' => $storefront?->delivery_fee,
                'free_delivery_threshold' => $storefront?->free_delivery_threshold,
                'min_order_amount' => $storefront?->min_order_amount,
                'whatsapp_ordering_enabled' => (bool) $storefront?->whatsapp_ordering_enabled,
                'whatsapp_delivery_fallback' => (bool) $storefront?->whatsapp_delivery_fallback,
                'fawran_enabled' => (bool) $storefront?->fawran_enabled,
                'fawran_alias' => $storefront?->fawran_alias,
                'fawran_mobile' => $storefront?->fawran_mobile,
                'fawran_iban' => $storefront?->fawran_iban,
                'show_out_of_stock_online' => (bool) $storefront?->show_out_of_stock_online,
                'product_card_layout' => $storefront?->product_card_layout ?? 'vertical',
                'show_store_header' => (bool) ($storefront?->show_store_header ?? true),
                'show_store_gradient' => (bool) ($storefront?->show_store_gradient ?? true),
                'navbar_color' => $storefront?->navbar_color ?? '#17201e',
            ],
        ];
    }

    private function mediaUrl(?string $path): ?string
    {
        return MediaUrl::public($path);
    }
}
