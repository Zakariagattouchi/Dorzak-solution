<?php

namespace App\Models;

use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Concerns\BelongsToStore;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use BelongsToStore, HasFactory;

    protected $fillable = [
        'store_id', 'order_number', 'customer_id', 'customer_name', 'customer_phone',
        'status', 'payment_method', 'payment_status', 'currency_code', 'source', 'fulfillment', 'table_number',
        'delivery_address', 'delivery_address_details', 'delivery_city', 'delivery_latitude', 'delivery_longitude', 'subtotal', 'discount',
        'tax_rate', 'tax_amount', 'delivery_fee', 'total', 'notes',
        'delivery_provider_id', 'delivery_provider_name', 'delivery_distance_km', 'delivery_fee_status',
        'delivery_quote_id', 'delivery_provider_code', 'delivery_external_reference',
        'delivery_external_status', 'delivery_dispatched_at',
        'pickup_latitude', 'pickup_longitude', 'pickup_address',
        'payment_reference', 'payment_proof_path', 'placed_at', 'completed_at', 'cancelled_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_method' => PaymentMethod::class,
            'payment_status' => PaymentStatus::class,
            'source' => OrderSource::class,
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'table_number' => 'integer',
            'placed_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'delivery_latitude' => 'decimal:7',
            'delivery_longitude' => 'decimal:7',
            'delivery_distance_km' => 'decimal:2',
            'pickup_latitude' => 'decimal:7',
            'pickup_longitude' => 'decimal:7',
            'delivery_dispatched_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
