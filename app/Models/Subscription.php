<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',    
        'payment_order_id',    
        'plan_id',
        'paid_amount',
        'purchased_credits',
        'previous_expired_credits',
        'total_credits',
        'expiry_date',
        'payment_status',
    ];
    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
