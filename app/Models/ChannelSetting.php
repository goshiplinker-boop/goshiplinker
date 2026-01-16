<?php

namespace App\Models;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChannelSetting extends Model
{
    use HasFactory;
    // Define the fillable attributes
    protected $fillable = [
        'channel_title',
        'channel_url',
        'client_id',
        'secret_key',
        'brand_logo',
        'brand_name',
        'company_id',
        'channel_id',
        'channel_code',
        'webhooks_create',
        'other_details',
        'status',
    ];
    public function Order()
    {
        return $this->belongsTo(Order::class);
    }
    public function shipmentInfos()
    {
        return $this->hasMany(ShipmentInfo::class); 
    }
   
}
