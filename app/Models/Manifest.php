<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ManifestOrder; // Correct namespace for ManifestOrder

class Manifest extends Model
{
    protected $fillable = [
        'company_id',
        'courier_id',
        'pickup_location_id',
        'pickup_created',
        'payment_mode',
    ];

    public function orders()
    {
        return $this->hasManyThrough(
            Order::class,
            ManifestOrder::class,
            'manifest_id',
            'id',
            'id',
            'order_id'
        );
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function pickupLocation()
    {
        return $this->belongsTo(PickupLocation::class, 'pickup_location_id');
    }

    public function courierSettings()
    {
        return $this->hasOne(CourierSetting::class, 'courier_id','courier_id');
    }

    // public function courier()
    // {
    //     return $this->belongsTo(Courier::class, 'courier_id');
    // }

    // public function orderProducts()
    // {
    //     return $this->hasMany(OrderProduct::class, 'manifest_id', 'id');
    // }
    public function manifestOrders()
    {
        return $this->hasMany(ManifestOrder::class, 'manifest_id');
    }
    public function scopeManifestedOrders($query)
    {
        return $query->whereHas('manifestOrder', function ($q) {
            // Add additional constraints if needed
        });
    }
}
