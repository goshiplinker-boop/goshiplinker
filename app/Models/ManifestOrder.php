<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManifestOrder extends Model
{
    protected $fillable = [
        'manifest_id',
        'order_id',
        'vendor_order_id',
        'tracking_number',
        
    ];
    
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function manifest()
    {
        return $this->belongsTo(Manifest::class);
    }

   
}
