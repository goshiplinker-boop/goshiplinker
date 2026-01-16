<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderLog extends Model
{
    protected $fillable = [
        'company_id',
        'order_id',
        'vendor_order_id',
        'type',
        'payload',
        'response',
        'status'
    ];
    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
    ];

    /**
     * Get the order associated with the log.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function shipmentInfo()
{
    return $this->belongsTo(ShipmentInfo::class);  // Each order log belongs to one shipment info
}


}
