<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'sales_channels',
        'couriers',
        'pickup_locations',
        'price_per_month',
        'setup_fee',
        'support_type',
        'status',
    ];

    public function durations()
    {
        return $this->hasMany(PlanDuration::class);
    }
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }
}
