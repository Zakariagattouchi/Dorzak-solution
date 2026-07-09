<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorefrontSetting extends Model
{
    protected $fillable = [
        'store_id', 'online_store_enabled', 'slug', 'bio', 'banner_path', 'logo_path',
        'accent_color', 'secondary_color', 'allow_delivery', 'allow_pickup',
        'allow_dine_in', 'dine_in_table_count',
        'delivery_fee', 'free_delivery_threshold', 'min_order_amount',
        'whatsapp_ordering_enabled', 'fawran_enabled', 'fawran_alias', 'fawran_mobile',
        'fawran_iban', 'show_out_of_stock_online',
        'product_card_layout', 'show_store_header', 'show_store_gradient',
        'navbar_color',
    ];

    protected function casts(): array
    {
        return [
            'online_store_enabled' => 'boolean',
            'allow_delivery' => 'boolean',
            'allow_pickup' => 'boolean',
            'allow_dine_in' => 'boolean',
            'dine_in_table_count' => 'integer',
            'whatsapp_ordering_enabled' => 'boolean',
            'fawran_enabled' => 'boolean',
            'show_out_of_stock_online' => 'boolean',
            'show_store_header' => 'boolean',
            'show_store_gradient' => 'boolean',
            'delivery_fee' => 'decimal:2',
            'free_delivery_threshold' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function publicUrl(): ?string
    {
        if (! $this->slug) {
            return null;
        }

        return rtrim((string) config('app.frontend_url', config('app.url')), '/')."/store/{$this->slug}";
    }
}
