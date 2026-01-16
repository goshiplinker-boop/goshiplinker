<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderTotal extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id', 
        'title', 
        'code', 
        'value', 
        'sort_order'
    ];

    // Relationship with Order
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
