<?php

namespace App\Services;

use App\Models\SettingsAuditLog;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Applies a single settings-group write to the right target (the stores row or one
 * of its 1:1 settings rows), records an audit trail entry, and returns the store
 * with settings relations reloaded for the response envelope. See docs 02 (Page 12),
 * 05 (Settings module), 12 §8.
 */
class SettingsService
{
    /** API field name => target column, for the groups that rename fields. */
    private const RENAMES = [
        'general' => ['business_name' => 'name'],
        'storefront' => [
            'store_slug' => 'slug',
            'store_bio' => 'bio',
            'banner_url' => 'banner_path',
            'logo_url' => 'logo_path',
        ],
    ];

    /** @var array<string, list<string>> group => allowed API field names */
    private const FIELDS = [
        'general' => ['business_name', 'tagline', 'phone', 'whatsapp', 'language'],
        'business' => ['owner_name', 'email', 'address', 'city', 'state', 'zip_code', 'country'],
        'currency' => ['currency', 'symbol_placement'],
        'taxes' => ['charge_sales_tax', 'tax_rate', 'tax_id', 'tax_included_in_price'],
        'receipts' => ['header', 'footer', 'show_logo', 'show_address', 'show_tax', 'auto_print'],
        'integrations' => ['facebook_pixel_id', 'google_analytics_id', 'facebook_connected'],
        'storefront' => [
            'online_store_enabled', 'store_slug', 'store_bio', 'banner_url', 'logo_url',
            'accent_color', 'allow_delivery', 'allow_pickup', 'allow_dine_in', 'dine_in_table_count', 'delivery_fee',
            'free_delivery_threshold', 'min_order_amount', 'whatsapp_ordering_enabled',
            'fawran_enabled', 'fawran_alias', 'fawran_mobile', 'fawran_iban', 'show_out_of_stock_online',
            'product_card_layout', 'show_store_header', 'show_store_gradient',
            'navbar_color',
        ],
    ];

    public function update(Store $store, string $group, array $data, ?User $actor): Store
    {
        return DB::transaction(function () use ($store, $group, $data, $actor): Store {
            [$target, $attributes] = $this->resolve($store, $group, $data);

            $target->fill($attributes);
            $new = $target->getDirty();

            if ($new !== []) {
                $old = array_intersect_key($target->getRawOriginal(), $new);
                $target->save();

                SettingsAuditLog::create([
                    'store_id' => $store->id,
                    'user_id' => $actor?->id,
                    'group' => $group,
                    'old_values' => $old,
                    'new_values' => $new,
                ]);
            }

            return $store->fresh(['storefrontSetting', 'receiptSetting', 'integrationSetting']);
        });
    }

    /**
     * Resolve the target model + the column=>value attributes for a group write.
     *
     * @return array{0: Model, 1: array<string, mixed>}
     */
    private function resolve(Store $store, string $group, array $data): array
    {
        // The payments group stores both the enabled-methods boolean set
        // (collapsed into accepted_payment_methods JSON) and the extended
        // payment configuration (bank details, WhatsApp phone, etc.) in
        // the payment_details JSON column.
        if ($group === 'payments') {
            $methods = [];
            foreach (['cash' => 'CASH', 'card' => 'CARD', 'transfer' => 'TRANSFER', 'whatsapp' => 'WHATSAPP'] as $key => $method) {
                if (! empty($data[$key])) {
                    $methods[] = $method;
                }
            }

            $attributes = ['accepted_payment_methods' => $methods];

            // Extract extended payment details (bank info, WhatsApp phone)
            $detailsKeys = ['bank_name', 'bank_account_name', 'bank_account_number', 'bank_iban', 'whatsapp_phone'];
            $details = array_intersect_key($data, array_flip($detailsKeys));
            if ($details !== []) {
                $attributes['payment_details'] = $details;
            }

            return [$store, $attributes];
        }

        $target = match ($group) {
            'general', 'business', 'currency', 'taxes' => $store,
            'receipts' => $store->receiptSetting()->firstOrCreate([]),
            'integrations' => $store->integrationSetting()->firstOrCreate([]),
            'storefront' => $store->storefrontSetting()->firstOrCreate([]),
        };

        // Keep only the group's known fields, then apply any API->column renames.
        $data = array_intersect_key($data, array_flip(self::FIELDS[$group]));
        $renames = self::RENAMES[$group] ?? [];
        $attributes = [];
        foreach ($data as $key => $value) {
            $attributes[$renames[$key] ?? $key] = $value;
        }

        // Business rule: QAR always renders the symbol before the amount.
        if ($group === 'currency' && ($attributes['currency'] ?? null) === 'QAR') {
            $attributes['symbol_placement'] = 'BEFORE';
        }

        return [$target, $attributes];
    }
}
