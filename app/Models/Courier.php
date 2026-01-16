<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $fillable = [
        'name',
        'parent_id',
        'company_id',
        'image_url',
        'status',
    ];

    /**
        * Get the parent Courier of this Courier.
        */
    public function parent()
    {
        return $this->belongsTo(Courier::class, 'parent_id');
    }

    /**
        * Get the child Courier of this Courier.
        */
    public function children()
    {
        return $this->hasMany(Courier::class, 'parent_id');
    }

    /**
        * Get the company that owns the carrier.
        */
    public function company()
    {
        return $this->belongsTo(Company::class); // assuming you have a Company model
    }
    public function courierSettings()
    {
        return $this->hasOne(CourierSetting::class, 'courier_id');
    }
    public function importPincodeNumbers()
    {
        return $this->hasMany(ImportPincodeNumber::class, 'courier_id');
    }
}