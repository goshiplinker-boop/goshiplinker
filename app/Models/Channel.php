<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'parent_id',
        'company_id',
        'image_url',
        'status',
    ];

    /**
     * Get the parent channel of this channel.
     */
    public function parent()
    {
        return $this->belongsTo(Channel::class, 'parent_id');
    }

    /**
     * Get the child channels of this channel.
     */
    public function children()
    {
        return $this->hasMany(Channel::class, 'parent_id');
    }

    /**
     * Get the company that owns the channel.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    
    public function orders()
    {
        return $this->hasMany(Order::class, 'channel_id');
    }
    public function channelSetting()
    {
        return $this->belongsTo(ChannelSetting::class, 'channel_id'); // Assuming channel_id is the foreign key in channel_settings
    }
}
