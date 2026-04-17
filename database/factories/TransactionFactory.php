<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
        return [
            'wallet_id' => \App\Models\Wallet::factory(),
            'type' => fake()->randomElement(['deposit', 'withdrawal', 'rebate']),
            'amount' => fake()->randomFloat(2, 0, 1000),
        ];
}
