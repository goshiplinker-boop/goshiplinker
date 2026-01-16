<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMapping extends Model
{
    use HasFactory;
    protected $fillable = [
        'payment_mode',
        'gateway_name',
        'channel_id',
        'company_id',
        'status',
    ];
     // Define relationships
     public function company()
     {
         return $this->belongsTo(Company::class);
     }
 
     public function channel()
     {
         return $this->belongsTo(Channel::class);
     }
}
