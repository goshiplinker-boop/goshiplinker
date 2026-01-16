<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pickup extends Model
{
    protected $table = 'pickup';

    protected $fillable = [
        'manifest_id',
        'order_id',
        'pickup_id',
        'pickup_time',
        'api_response',
    ];
}
