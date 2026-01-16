<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPackage extends Model
{
    protected $table = 'order_packages';

    protected $fillable = [
        'order_id',
        'package_code',
        'length',
        'breadth',
        'height',
        'dead_weight',
    ];
    // Relationships
    public function order()
    {
        return $this->belongsTo(\App\Models\Order::class);
    }
}
