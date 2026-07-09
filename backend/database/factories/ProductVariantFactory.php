<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => fake()->randomElement(['Small', 'Medium', 'Large']).' / '.fake()->safeColorName(),
            'price' => fake()->randomFloat(2, 5, 200),
            'stock' => fake()->numberBetween(0, 50),
            'sku' => 'VAR-'.strtoupper(fake()->unique()->bothify('####??')),
            'sort_order' => 0,
        ];
    }
}
