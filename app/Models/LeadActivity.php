<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadActivity extends Model
{
    use HasFactory;
    protected $fillable = [
        'lead_status_id',
        'last_remarks',// This will store the last remark
        'user_id',
        'company_id',
        'followup_date',
        'is_followup_completed',
        'remarks',
    ];

    /**
     * Define relationships.
     */

    /**
     * Override the save method to set last_activity based on remarks.
     */
    public function save(array $options = [])
    {
        if ($this->isDirty('remarks')) {
            $this->last_remarks = $this->remarks; // Set last_activity to the value of remarks
        }

        parent::save($options);
    }

    // A lead activity belongs to a company
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    // A lead activity belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // A lead activity belongs to a lead status
    public function leadStatus()
    {
        return $this->belongsTo(LeadStatus::class, 'lead_status_id');
    }

}
