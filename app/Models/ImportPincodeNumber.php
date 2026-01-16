<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportPincodeNumber extends Model
{
    protected $table = 'import_pincode_numbers';
    public $timestamps = false; // Disable timestamps
    protected $fillable = [
        'courier_id',
        'company_id',
        'zipcodes',
        'payment_type',
    ];
    // Relation with Courier
    public function courier()
    {
        return $this->belongsTo(Courier::class, 'courier_id');
    }
}

