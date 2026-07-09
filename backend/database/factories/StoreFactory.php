<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    protected $model = Store::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'owner_name' => fake()->name(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'country' => 'United States',
            'currency' => 'USD',
            'symbol_placement' => 'BEFORE',
            'language' => 'en',
            'tax_rate' => 8.5,
        ];
    }

    /** Every factory-created store gets its three 1:1 settings rows. */
    public function configure(): static
    {
        return $this->afterCreating(fn (Store $store) => $store->initializeSettings());
    }
}
