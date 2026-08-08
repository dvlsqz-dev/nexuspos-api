<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => \App\Models\Tenant::factory(),
            'name' => $this->faker->words(3, true),
            'sku' => $this->faker->unique()->bothify('SKU-####'),
            'price' => $this->faker->randomFloat(2, 5, 500),
            'cost' => $this->faker->randomFloat(2, 2, 300),
            'tracks_batches' => false,
            'track_inventory' => true,
            'is_active' => true,
        ];
    }
}
