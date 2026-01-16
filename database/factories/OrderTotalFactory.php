<?php

namespace Database\Factories;

use App\Models\OrderTotal;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderTotalFactory extends Factory
{
    protected $model = OrderTotal::class;

    public function definition()
    {
        $subtotal = $this->faker->randomFloat(2, 50, 500);
        $shipping = $this->faker->randomFloat(2, 5, 50);
        $discount = $this->faker->randomFloat(2, 0, 100);  
        $tax = $this->faker->randomFloat(2, 5, 50);
        $codCharges = $this->faker->randomFloat(2, 0, 30);
        $giftwrap = $this->faker->randomFloat(2, 0, 10);
        $total = $subtotal + $shipping + $codCharges + $giftwrap + $tax - $discount;
        $order_totals = [];
        $order_totals[] = [
            "title" => "Subtotal",
            "code" => "sub_total",
            "value" => $subtotal,
            "sort_order" => 1
        ];
        if ($shipping > 0) {
            $order_totals[] = [
                "title" => "Shipping",
                "code" => "shipping",
                "value" => $shipping,
                "sort_order" => 2
            ];
        }
        if ($tax > 0) {
            $order_totals[] = [
                "title" => "Tax",
                "code" => "tax",
                "value" => $tax,
                "sort_order" => 3
            ];
        }
        if ($discount > 0) {
            $order_totals[] = [
                "title" => "Discount",
                "code" => "discount",
                "value" => $discount,
                "sort_order" => 4
            ];
        }
        if ($giftwrap > 0) {
            $order_totals[] = [
                "title" => "Giftwrap",
                "code" => "giftwrap",
                "value" => $giftwrap,
                "sort_order" => 5
            ];
        }
        if ($codCharges > 0) {
            $order_totals[] = [
                "title" => "CODcharges",
                "code" => "codCharges",
                "value" => $codCharges,
                "sort_order" => 6
            ];
        }
        $order_totals[] = [
            "title" => "Total",
            "code" => "total",
            "value" => $total,
            "sort_order" => 9
        ];
        $order_total = $this->faker->randomElement($order_totals);
         return [
            'order_id' => Order::factory(), 
            'title' => $order_total['title'],
            'code' => $order_total['code'],
            'value' => $order_total['value'],
            'sort_order' => $order_total['sort_order'],
        ];
    }
}
