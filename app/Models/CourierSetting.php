<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourierSetting extends Model
{
    use HasFactory;
     // Define which attributes are mass assignable
     protected $fillable = [
        'company_id',
        'courier_id',
        'courier_code',
        'courier_title',
        'courier_details',
        'env_type',
        'status',
    ];
    public function courier()
    {
        return $this->belongsTo(Courier::class, 'courier_id');
    }
    public function Order()
    {
        return $this->belongsTo(Order::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class, 'courier_id');
    }
    public function importPincodeNumbers()
    {
        return $this->hasMany(ImportPincodeNumber::class, 'courier_id');
    }
    public function sellersCouriers()
    {
        return $this->hasMany(SellersCourier::class, 'courier_id', 'courier_id');
    }
}
