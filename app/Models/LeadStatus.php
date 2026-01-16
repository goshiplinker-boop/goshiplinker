<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadStatus extends Model
{
    public function companies()
    {
        return $this->hasMany(Company::class);
    }

    // A lead status can have many lead activities
    public function leadActivities()
    {
        return $this->hasMany(LeadActivity::class);
    }
}