<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManageTrackingPage extends Model
{
    protected $table = 'manage_tracking_page';
    protected $fillable = [
        'company_id',
        'website_domain',
        'custom_style_script',
        'json_data',
        'status',
    ];

}
