<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    use HasFactory;
    protected $fillable = ['status_code', 'status_name', 'status']; // Fillable attributes


    public function showOrders()
    {
        // Fetch all records from both the orders and order_statuses tables
        $orders = Order::all();          // Fetch all orders
        $orderStatuses = OrderStatus::all(); // Fetch all order statuses

        // Pass both variables to the view
        return view('order_list', compact('orders', 'orderStatuses'));
    }
}
