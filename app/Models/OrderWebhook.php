<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderWebhook extends Model
{
    // The attributes that are mass assignable
    protected $fillable = [
        'company_id',
        'order_id',
        'channel_id',
        'channel_order_id',
        'webhook_type',
        'status',
        'webhook_data',
    ];

    // Define status constants
    const STATUS_PENDING = '0';
    const STATUS_SUCCESS = '1';
    const STATUS_FAILED = '2';

    // Define any relationships if applicable
    public function company()
    {
        return $this->belongsTo(Company::class); // Adjust if you have a Company model
    }

    public function order()
    {
        return $this->belongsTo(Order::class); // Adjust if you have an Order model
    }

    // Method to check if status is pending
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    // Method to check if status is successful
    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    // Method to check if status is failed
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }
}