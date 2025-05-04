<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $today = today();

        return [
            'customer_id' => 1,
            'order_date' => $today,
            'receivable' => 100,
            'paid' => 50,
            'due' => 50,
            'note' => 'note',
            'total_amount' => 0,
            'tenant_id' => 1,
        ];
    }
}
