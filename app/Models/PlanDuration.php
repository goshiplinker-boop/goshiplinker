<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanDuration extends Model
{
    protected $fillable = [
        'plan_id',
        'duration_months',
        'shipment_credits',
        'total_amount',
        'discount',
        'status',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
