<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Orderitem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 'order_id'=>Order::factory()->create(),
            'product_id' => 1,
            'rate' => 1,
            'total_rate' => 1,
            'main_unit_qty' => 1,
            'sub_unit_qty' => 1,
            'total_qty' => 1,
            'total_in_text' => 1,
            'available_qty' => 1,
            'purchase_cost' => 1,
            'purchase_ids' => [],
            'tenant_id' => 1,
        ];
    }
}
