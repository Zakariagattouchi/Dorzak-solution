<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $price = fake()->randomFloat(2, 5, 200);

        return [
            'store_id' => Store::factory(),
            'category_id' => null,
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'price' => $price,
            'cost' => round($price * fake()->randomFloat(2, 0.3, 0.6), 2),
            'sku' => 'SKU-'.strtoupper(fake()->unique()->bothify('####??')),
            'unit' => 'pcs',
            'taxable' => true,
            'track_stock' => true,
            'stock' => fake()->numberBetween(0, 150),
            'min_stock' => 5,
            'show_in_online_store' => true,
            'is_featured' => false,
            'is_active' => true,
        ];
    }

    public function lowStock(): static
    {
        return $this->state(['stock' => 3, 'min_stock' => 5]);
    }

    public function outOfStock(): static
    {
        return $this->state(['stock' => 0]);
    }

    public function hiddenOnline(): static
    {
        return $this->state(['show_in_online_store' => false]);
    }

    public function nonTaxable(): static
    {
        return $this->state(['taxable' => false]);
    }
}
