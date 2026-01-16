<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsDltSetting extends Model
{
    protected $fillable = [
        'company_id',
        'header_id',
        'header_registration_id',
        'telecom_provider_name',
        'company_legal_name',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
