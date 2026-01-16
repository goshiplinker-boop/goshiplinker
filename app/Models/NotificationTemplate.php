<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'company_id',
        'channel',
        'user_type',
        'event_type',
        'body',
        'meta',
        'status'
    ];

    protected $casts = [
        'meta' => 'array',
        'status' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
     // Prevent duplicate templates
     public static function createUniqueTemplate($data)
     {
         return self::firstOrCreate([
             'company_id' => $data['company_id'],
             'channel' => $data['channel'],
             'user_type' => $data['user_type'],
             'event_type' => $data['event_type'],
         ], $data);
     }
     public function notifications()
     {
         return $this->hasMany(Notification::class);
     }

}
