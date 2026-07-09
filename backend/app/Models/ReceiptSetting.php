<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceiptSetting extends Model
{
    protected $fillable = [
        'store_id', 'header', 'footer', 'show_logo', 'show_address', 'show_tax', 'auto_print',
    ];

    protected function casts(): array
    {
        return [
            'show_logo' => 'boolean',
            'show_address' => 'boolean',
            'show_tax' => 'boolean',
            'auto_print' => 'boolean',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
