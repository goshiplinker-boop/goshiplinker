<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class ShipmentInfo extends Model
{
    use HasFactory;

    protected $table = 'shipment_info';

    protected $fillable = [
        'order_id',
        'company_id',
        'shipment_type',
        'courier_id',
        'tracking_id',
        'applied_weight',
        'fulfillment_status',
        'current_status',
        'store_shipment_status',
        'current_status_date',
        'origin',
        'destination',
        'pickedup_date',
        'pickedup_location_id',
        'pickedup_location_address',
        'edd',
        'pod',
        'return_location_id',
        'return_location_address',
        'manitest_created',
        'pickup_id',
        'payment_mode',
        'label_generated'
    ];
    public function order()
    {
        return $this->belongsTo(Order::class,'order_id');  // Assuming a shipment info belongs to an order
    }
    public function orderLogs()
    {
        return $this->hasMany(OrderLog::class);  // A shipment info can have many order logs
    }
    public function channelSetting()
    {
        return $this->belongsTo(ChannelSetting::class);  // Assuming a shipment info belongs to a single channel setting
    }
     public function trackingHistory()
    {
        return $this->hasMany(TrackingHistory::class, 'shipment_info_id', 'id'); 
        // Adjust 'shipment_info_id' and 'id' to match your database schema
    }
    public function courierSetting()
    {
        return $this->belongsTo(CourierSetting::class, 'courier_id','courier_id');
    }
    public function courierResponse()
    {
        return $this->hasOne(OrderCourierResponse::class, 'order_id', 'order_id');
    }
    public function weightDiscrepancies()
    {
        return $this->hasMany(WeightDiscrepancy::class,'shipment_id','id');
    }

    public function latestWeightDiscrepancy()
    {
        return $this->hasOne(WeightDiscrepancy::class,'shipment_id','id')->latestOfMany();
    }
    public function courier()
    {
        return $this->belongsTo(Courier::class, 'courier_id');
    }
}