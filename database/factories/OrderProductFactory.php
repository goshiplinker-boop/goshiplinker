<?php

namespace Database\Factories;

use App\Models\OrderProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderProductFactory extends Factory
{
    public function definition()
    {
        $unitPrice = $this->faker->randomFloat(2, 50, 100);
        $quantity =1;
        $totalPrice = $unitPrice * $quantity;

        return [
            'product_name' => $this->faker->unique()->words(3, true),
            'sku' => $this->faker->unique()->lexify('???-???'),
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'discount' => 0,
            'shipping' => 0,
            'hsn' => $this->faker->numerify('####'),
            'tax_rate' => 0,
            'tax_amount' => 0,
            'total_price' => $totalPrice
        ];
    }
}
