<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingHistory extends Model
{   
    protected $table = 'tracking_history';
    protected $fillable = [
        'order_id',
        'courier_id',
        'tracking_number',
        'current_shipment_status',
        'current_shipment_status_code',
        'current_shipment_status_date',
        'current_shipment_location',
    ];

    public function order()
{
    return $this->belongsTo(Order::class);
}

}
