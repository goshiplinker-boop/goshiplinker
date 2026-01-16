<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
class OrderCourierResponse extends Model
{
    protected $fillable = [
        'order_id',
        'courier_code',
        'courier_name',
        'response',
    ];
    protected $casts = [
        'response' => 'array',
    ];
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function shipmentInfo()
    {
        return $this->hasOne(ShipmentInfo::class, 'order_id', 'order_id');
    }
}
