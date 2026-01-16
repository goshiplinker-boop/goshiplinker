<?php

namespace App\Models;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'product_name',
        'sku',
        'unit_price',
        'quantity',
        'discount',
        'shipping',
        'hsn',
        'tax_rate',
        'tax_type',
        'tax_name',
        'tax_amount',
        'total_price',
        'line_item_id',
        'fulfillment_id'
    ];

    // Define relationships
    public function order()
    {
        return $this->belongsTo(Order::class,'id');
    }

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class, 'order_id', 'id');
    }


    public function manifest()
    {
        return $this->belongsTo(Manifest::class, 'manifest_id');
    }
}