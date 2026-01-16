<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;
    // Disable automatic timestamps
    public $timestamps = false;
    protected $fillable = [
        'country_name',
        'country_code',
        'alpha_3',
        'dialing_code',
        'status',
    ];

    public function companies()
    {
        return $this->hasMany(Company::class, 'country_code', 'country_code');
    }
}
