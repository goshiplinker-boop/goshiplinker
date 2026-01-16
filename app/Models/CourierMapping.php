<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class CourierMapping extends Model
{  
    use HasFactory;
    protected $fillable = [
        'courier_id',
        'courier_name',
        'channel_id',
        'company_id',
        'status',
    ];
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function channel()
    {
        return $this->belongsTo(Channel::class, 'channel_id');
    }
    public function courier()
    {
        return $this->belongsTo(Courier::class, 'courier_id');
    }
}
