<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'nit' => $this->faker->numerify('########'),
            'business_type' => 'Tienda de abarrotes',
            'address' => $this->faker->address(),
            'phone' => $this->faker->numerify('########'),
            'tax_regime' => 'general',
            'default_tax_rate' => 12.00,
            'prices_include_tax' => true,
            'currency' => 'GTQ',
            'is_active' => true,
            'status' => 'pendiente',
            'ticket_paper_width' => '80mm',
        ];
    }
}
