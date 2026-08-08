<?php

namespace Database\Factories;

use App\Models\CashSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CashSession>
 */
class CashSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cash_register_id' => \App\Models\CashRegister::factory(),
            'user_id' => \App\Models\User::factory(),
            'opening_amount' => 100.00,
            'closing_amount' => null,
            'status' => 'abierto',
            'opened_at' => now(),
            'closed_at' => null,
        ];
    }
}
