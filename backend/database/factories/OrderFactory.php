<?php

namespace Database\Factories;

use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 *
 * Produces internally consistent money (total = subtotal - discount + tax). For
 * realistic end-to-end flows create orders through OrderService instead.
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 20, 300);
        $tax = round($subtotal * 0.085, 2);

        return [
            'store_id' => Store::factory(),
            'order_number' => 'ORD-'.fake()->unique()->numberBetween(1000, 9999),
            'customer_name' => fake()->name(),
            'customer_phone' => '+1 555-'.fake()->numerify('####'),
            'status' => OrderStatus::COMPLETE->value,
            'payment_method' => fake()->randomElement(PaymentMethod::values()),
            'source' => OrderSource::POS->value,
            'subtotal' => $subtotal,
            'discount' => 0,
            'tax_rate' => 8.5,
            'tax_amount' => $tax,
            'delivery_fee' => 0,
            'total' => round($subtotal + $tax, 2),
            'placed_at' => now(),
            'completed_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => OrderStatus::CONFIRMING->value, 'completed_at' => null]);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => OrderStatus::CANCELLED->value, 'cancelled_at' => now()]);
    }

    public function online(): static
    {
        return $this->state(['source' => OrderSource::ONLINE->value, 'payment_method' => PaymentMethod::WHATSAPP->value]);
    }
}
