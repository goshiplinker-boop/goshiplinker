<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerPincode extends Model
{
    protected $table = 'seller_pincodes';

    protected $fillable = [
        'company_id',
        'courier_id',
        'pincode',
        'status',
    ];

    /* ===============================
     | Relationships
     =============================== */

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    /**
     * IMPORTANT:
     * courier_id → couriers.parent_id
     */
    public function courier()
    {
        return $this->belongsTo(
            Courier::class,
            'courier_id',   // FK in seller_pincodes
            'parent_id'     // referenced column in couriers
        );
    }

    /* ===============================
     | Scopes
     =============================== */

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
