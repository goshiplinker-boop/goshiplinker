<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class PincodeMaster extends Model
{
    use HasFactory;

    protected $table = 'pincode_master';

    protected $fillable = [
        'company_id',
        'courier_id',
        'pincode',
        'city',
        'state',
        'route_code',
        'forward_pickup',
        'forward_delivery',
        'reverse_pickup',
        'cod',
        'prepaid',
        'status',
    ];

    // Optional relationship if you have a Company model
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function courier()
    {
        return $this->belongsTo(Courier::class, 'courier_id');
    }
}
