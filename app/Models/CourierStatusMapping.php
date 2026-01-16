<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourierStatusMapping extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'courier_id',
        'courier_code',
        'courier_status',
        'shipment_status_code',
    ];

}
