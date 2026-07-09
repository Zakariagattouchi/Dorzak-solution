<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationSetting extends Model
{
    protected $fillable = [
        'store_id', 'facebook_pixel_id', 'google_analytics_id', 'facebook_connected', 'facebook_page_name',
    ];

    protected function casts(): array
    {
        return [
            'facebook_connected' => 'boolean',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
