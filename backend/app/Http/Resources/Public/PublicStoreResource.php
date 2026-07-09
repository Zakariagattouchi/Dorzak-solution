<?php

namespace App\Http\Resources\Public;

use App\Enums\Currency;
use App\Models\Store;
use App\Services\DeliveryQuoteService;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Public store card. Exposes ONLY customer-facing fields — no owner email, tax id,
 * integration secrets, etc. (leak-tested, docs 12 §3).
 *
 * @mixin Store
 */
class PublicStoreResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $sf = $this->storefrontSetting;
        $currency = Currency::tryFrom((string) $this->currency);

        // How this store can actually deliver: 'quoted' (priced at checkout),
        // 'whatsapp_pending' (merchant quotes over WhatsApp) or 'none'. NOTE:
        // this response is cached (CatalogCache, ~60s TTL) — plan/provider
        // changes may take up to the TTL to reflect here.
        $deliveryMode = app(DeliveryQuoteService::class)->storeMode($this->resource);

        return [
            'business_name' => $this->name,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'currency' => $this->currency,
            'currency_symbol' => $currency?->symbol(),
            'symbol_placement' => $this->symbol_placement,
            'bio' => $sf?->bio,
            'banner_url' => $this->mediaUrl($sf?->banner_path),
            'logo_url' => $this->mediaUrl($sf?->logo_path),
            'accent_color' => $sf?->accent_color,
            'secondary_color' => $sf?->secondary_color,
            'allow_delivery' => (bool) $sf?->allow_delivery && $deliveryMode !== 'none',
            'delivery_mode' => $deliveryMode,
            'allow_pickup' => (bool) $sf?->allow_pickup,
            'allow_dine_in' => (bool) $sf?->allow_dine_in,
            'dine_in_table_count' => (int) ($sf?->dine_in_table_count ?? 0),
            'delivery_fee' => $sf?->delivery_fee,
            'free_delivery_threshold' => $sf?->free_delivery_threshold,
            'min_order_amount' => $sf?->min_order_amount,
            'whatsapp_ordering_enabled' => (bool) $sf?->whatsapp_ordering_enabled,
            'fawran_enabled' => (bool) $sf?->fawran_enabled,
            'fawran_alias' => $sf?->fawran_alias,
            'fawran_mobile' => $sf?->fawran_mobile,
            'fawran_iban' => $sf?->fawran_iban,
            'product_card_layout' => $sf?->product_card_layout ?? 'vertical',
            'show_store_header' => (bool) ($sf?->show_store_header ?? true),
            'show_store_gradient' => (bool) ($sf?->show_store_gradient ?? true),
            'navbar_color' => $sf?->navbar_color ?? '#17201e',
        ];
    }

    private function mediaUrl(?string $path): ?string
    {
        return MediaUrl::public($path);
    }
}
