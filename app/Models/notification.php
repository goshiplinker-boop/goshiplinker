<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class notification extends Model
{
    protected $fillable = [
        'order_id',
        'company_id',
        'channel_id',
        'user_type',
        'event',
        'customer_id',
        'response',
        'sent_status',
        
    ];
    public function notificationTemplate()
    {
        return $this->belongsTo(NotificationTemplate::class);
    }
    
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    

}