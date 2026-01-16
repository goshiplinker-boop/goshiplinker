<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class SellersCourier extends Model
{
    use HasFactory;

    protected $table = 'sellers_couriers';

    protected $fillable = [
        'courier_id',
        'company_id',
        'seller_courier_status',
        'main_courier_status',
    ];
    public function courierSetting()
    {
        return $this->belongsTo(CourierSetting::class, 'courier_id', 'courier_id');
    }
}
