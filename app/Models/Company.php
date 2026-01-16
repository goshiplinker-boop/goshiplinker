<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'companies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parent_id',
        'legal_registered_name',
        'pan_number',
        'pan_image',
        'company_email_id',
        'phone_number',
        'brand_name',
        'brand_logo',
        'website_url',
        'address',
        'pincode',
        'city',
        'state_code',
        'country_code',
        'shipment_weight',
        'channel_name',
        'courier_using',
        'product_category',
        'monthly_orders',
        'lead_status_id',
        'subscription_plan',
        'subscription_status',
        'company_type_id',
        'doc_type',
        'doc_number',
        'doc_urls',        
        'utm_data',
        'bank_details',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'boolean',
    ];
    public function orders()
    {
        return $this->hasMany(Order::class, 'company_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'company_id');
    }

    // A company has many lead activities
    public function leadActivities()
    {
        return $this->hasMany(LeadActivity::class, 'company_id', 'id');
    }

    // A company belongs to a lead status
    public function leadStatus()
    {
        return $this->belongsTo(LeadStatus::class, 'lead_status_id');
    }

    // A company belongs to a country
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_code', 'country_code');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'company_id');
    }

    public function latestLeadActivity()
    {
        return $this->hasOne(LeadActivity::class)
                    ->latest('followup_date'); // Get the latest followup_date
    }
    public function channel()
    {
        return $this->hasOne(Channel::class); 
    }
    public function notificationTemplates()
    {
        return $this->hasMany(NotificationTemplate::class);
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'company_id');
    }
    public function wallet()
    {
        return $this->hasOne(SellerWallet::class, 'company_id', 'id');
    }

    public function walletLedgers()
    {
        return $this->hasMany(SellerWalletLedger::class, 'company_id');
    }

    public function weightDiscrepancies()
    {
        return $this->hasMany(WeightDiscrepancy::class, 'company_id');
    }
}