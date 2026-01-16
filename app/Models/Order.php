<?php

namespace App\Models;
use App\Models\ChannelSetting;
use App\Models\ShipmentInfo;
use App\Models\CourierSetting;
use App\Models\TrackingHistory;
use App\Models\Courier;
use App\Models\OrderCourierResponse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Order extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $fillable = [
        'vendor_order_id',
        'vendor_order_number',
        'channel_id',
        'channel_order_date',
        'status_code',
        'financial_status',
        'customer_id',
        'company_id',
        'fullname',
        'email',
        'phone_number',
        's_fullname',
        's_company',
        's_complete_address',
        's_landmark',
        's_phone',
        's_zipcode',
        's_city',
        's_state_code',
        's_country_code',
        'b_fullname',
        'b_company',
        'b_complete_address',
        'b_landmark',
        'b_phone',
        'b_zipcode',
        'b_city',
        'b_state_code',
        'b_country_code',
        'invoice_prefix',
        'invoice_number',
        'package_type',
        'package_length',
        'package_breadth',
        'package_height',
        'package_dead_weight',
        'notes',
        'order_tags',
        'payment_mode',
        'payment_method',
        'currency_code',
        'sub_total',
        'order_total',
        'customer_ip_address',
        'rate_card_id'
    ];
 
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function courier()
    {
        return $this->belongsTo(Courier::class, 'courier_id', 'id');
    }
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class, 'order_id', 'id');
    }
    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'status_code', 'status_code');
    }
    public function orderTotals()
    {
        return $this->hasMany(OrderTotal::class,'order_id')->orderBy('sort_order', 'asc');
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class, 'channel_id');
    }
    public function channelSetting()
    {
        return $this->hasOneThrough(ChannelSetting::class, Channel::class, 'id', 'channel_id', 'channel_id', 'id');
    }
    public function shipmentInfo()
    {
        return $this->hasOne(ShipmentInfo::class,'order_id', 'id' );
    }
    public function pickupLocation()
    {
        return $this->belongsTo(PickupLocation::class, 'pickup_location_id'); // Assuming 'pickup_location_id' is the foreign key
    }
    public function courierSetting()
    {
        return $this->hasOneThrough(
            CourierSetting::class,  // Final model
            ShipmentInfo::class,    // Intermediate model
            'order_id',             // FK in shipment_info (linking to orders)
            'courier_id',           // FK in courier_settings (linking to couriers)
            'id',                   // PK in orders
            'courier_id'            // FK in shipment_info (linking to courier_settings)
        );
    }
    // public function courierSetting()
    // { 
    //     return $this->hasOneThrough(CourierSetting::class, Courier::class, 'id', 'courier_id', 'courier_id', 'id');
    // }

    public function manifest()
    {
        return $this->hasOneThrough(
            Manifest::class,
            ManifestOrder::class,
            'order_id',
            'id',
            'id',
            'manifest_id'
        );
    }
    public function manifestOrders()
    {
        return $this->hasMany(ManifestOrder::class, 'order_id');
    }

    public function manifestOrder()
    {
        return $this->hasOneThrough(
            Manifest::class,
            ManifestOrder::class,
            'order_id',
            'id',
            'id',
            'manifest_id'
        );
    }
    public function manifests()
    {
        return $this->belongsToMany(
            Manifest::class,
            'manifest_orders',
            'order_id',
            'manifest_id'
        );
    }
    public function scopeNewOrders($query)
    {
        return $query->where('status_code', 'N');
    }

    public function scopeReadyToShipOrders($query)
    {
        return $query->where('status_code', 'P');
    }

    public function scopeManifestedOrders($query)
    {
        return $query->whereHas('manifestOrder', function ($q) {
            // Add additional constraints if needed
        });
    }
    public function scopeIntOrders($query)
    {
        return $query->whereHas('shipmentInfo', function ($query) {
            $query->where('current_status', 'INT');
        });
    }
    public function scopeRtoOrders($query)
    {
        return $query->whereHas('shipmentInfo', function ($query) {
            $query->where('current_status', 'RTO');
        });
    }
    public function scopeDelOrders($query)
    {
        return $query->whereHas('shipmentInfo', function ($query) {
            $query->where('current_status', 'DEL');
        });
    }
    public function orderLogs()
    {
        return $this->hasOne(OrderLog::class);  
    }

    public function trackingHistory()
    {
        return $this->hasMany(TrackingHistory::class, 'order_id', 'id');
    }

    public function manageTrackingPage()
    {
        return $this->belongsTo(ManageTrackingPage::class, 'company_id', 'company_id');
    }
    public function courierResponse()
    {
        return $this->hasOne(OrderCourierResponse::class);
    }
    public function packages()
    {
        return $this->hasMany(\App\Models\OrderPackage::class);
    }
    public function walletLedgers()
    {
        return $this->hasMany(SellerWalletLedger::class, 'order_id');
    }

    public function weightDiscrepancies()
    {
        return $this->hasMany(WeightDiscrepancy::class, 'order_id');
    }
}
