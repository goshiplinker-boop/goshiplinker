<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickupLocation extends Model
{
    use HasFactory;
    protected $fillable = [
        'location_title',
        'contact_person_name',
        'email',
        'phone',
        'alternate_phone',
        'address',
        'landmark',
        'zipcode',
        'city',
        'state_code',
        'country_code',
        'location_type',
        'pickup_day',
        'pickup_time',
        'brand_name',
        'company_id',
        'gstin',
        'courier_warehouse_id',
        'default',
        'status',
    ];
    protected $casts = [
        'default' => 'boolean',
        'status' => 'boolean',
    ];
}