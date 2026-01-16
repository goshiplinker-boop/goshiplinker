<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerPaymentHistory extends Model
{   

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'seller_payment_history';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'payment_order_id',
        'txn_id',
        'gateway',
        'amount',
        'status',
        'reason',
        'request_payload',
        'response',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'request_payload' => 'array',
        'response' => 'array',
    ];
}
