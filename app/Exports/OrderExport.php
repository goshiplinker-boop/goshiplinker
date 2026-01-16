<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\ShipmentStatus;
use App\Models\ManifestOrder;
use App\Models\Manifests;
use Carbon\Carbon;
class OrderExport implements FromQuery, WithMapping, WithHeadings, WithChunkReading, WithStrictNullComparison
{
    use Exportable;

    protected $query;
    protected $shipmentStatuses;
    protected $filters;
    protected $queryFilters;

    public function __construct($query, $queryFilters, $filters)
    {
        $this->query = $query;
        $this->queryFilters = $queryFilters;
        $this->filters = $filters;
        $this->shipmentStatuses = DB::table('shipment_statuses')->pluck('name', 'code');
    }

    public function query()
    {
        $query = $this->query;
        $filters = $this->filters;        
        // Apply basic filters
        foreach ($this->queryFilters as $key => $value) {
            is_array($value)
                ? $query->whereIn($key, $value)
                : $query->where($key, $value);
        }
        $query->whereNull('orders.deleted_at');

        // Date filters — explicitly on orders table
        if (isset($filters['startDate']) && !empty($filters['startDate'])) {
            $query->where("orders.channel_order_date", ">=", $filters['startDate']);
        }
        if (isset($filters['endDate']) && !empty($filters['endDate'])) {
            $query->where("orders.channel_order_date", "<=", $filters['endDate'] . " 23:59:59");
        }  
        /* Phone number (FIXED grouping) */
        if (!empty($filters['phone_number'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('orders.s_phone', $filters['phone_number'])
                ->orWhere('orders.b_phone', $filters['phone_number']);
            });
        }

        // Payment mode
        if (!empty($filters['payment_mode'])) {
            if ($filters['payment_mode'] === 'cod') {
                $query->where('orders.payment_mode', 'cod');
            } else {
                $query->where('orders.payment_mode', '!=', 'cod');
            }
        }

        // Order IDs
        if (!empty($filters['order_ids'])) {
            $order_ids = array_map('trim', explode(',', $filters['order_ids']));
            $query->whereIn('orders.id', $order_ids);
        }

        // Selected order IDs
        if (!empty($filters['selectedOrderIds'])) {
            $query->whereIn('orders.id', $filters['selectedOrderIds']);
        }

        // Vendor order numbers
        if (!empty($filters['vendor_order_numbers'])) {
            $numbers = array_map('trim', explode(',', $filters['vendor_order_numbers']));
            $query->whereIn('orders.vendor_order_number', $numbers);
        }

        // Channel ID
        if (!empty($filters['channel_id'])) {
            $query->where('orders.channel_id', $filters['channel_id']);
        }

        // Courier IDs
        if (!empty($filters['courier_ids'])) {
            $query->whereHas('shipmentInfo', function ($q) use ($filters) {
                $q->whereIn('courier_id', explode(',', $filters['courier_ids']));
            });
        }
        
        if (isset($filters['label_generated']) && $filters['label_generated'] !== '') {
            $query->where('shipmentInfo.label_generated', (int) $filters['label_generated']);
        }

        // Tracking numbers
        if (!empty($filters['tracking_numbers'])) {
            $query->whereHas('shipmentInfo', function ($q) use ($filters) {
                $q->whereIn('tracking_id', explode(',', $filters['tracking_numbers']));
            });
        }

        // Shipment status code
        if (!empty($filters['shipment_status_code'])) {
            $shipmentStatus = ShipmentStatus::where('status', '1')
                ->where('parent_code', $filters['shipment_status_code'])
                ->pluck('code');

            $query->whereHas('shipmentInfo', function ($q) use ($shipmentStatus) {
                $q->whereIn('current_status', $shipmentStatus);
            });
        }

        // Pickup location
        if (!empty($filters['pickup_location_id'])) {
            $query->whereHas('shipmentInfo', function ($q) use ($filters) {
                $q->where('pickedup_location_id', $filters['pickup_location_id']);
            });
        }

        // Order status codes
        if (!empty($filters['order_status_codes'])) {
            $query->whereIn('orders.status_code', $filters['order_status_codes']);
        }

        // SKU filter
        if (!empty($filters['sku'])) {
            $query->whereHas('orderProducts', function ($q) use ($filters) {
                $q->where('sku', 'LIKE', '%' . $filters['sku'] . '%');
            });
        }

        // Order tags
        if (!empty($filters['order_tags'])) {
            $tags = array_map('trim', explode(',', $filters['order_tags']));
            $query->whereIn('orders.order_tags', $tags);
        }

        // Order weight
        if (!empty($filters['order_weight'])) {
            $weights = array_map('trim', explode('-', $filters['order_weight']));
            if (count($weights) === 2) {
                $query->whereBetween('orders.package_dead_weight', [$weights[0], $weights[1]]);
            } else {
                $query->where('orders.package_dead_weight', '>', $weights[0]);
            }
        }

        // Order history
        if (!empty($filters['order_history'])) {
            $history = (int) $filters['order_history'];
            $query->whereRaw(
                '(SELECT COUNT(id) FROM orders o2 WHERE o2.customer_id = orders.customer_id) ' . ($history <= 3 ? '=' : '>=') . ' ?',
                [$history]
            );
        }

        // Load relationships
        $query->with([
            'orderProducts',
            'orderTotals',
            'shipmentInfo.courierSetting',
            'manifestOrders',
            'manifests',
            'channelSetting',
        ]);

        return $query;
    }

    public function map($order): array
    {
        $shipment_status = optional($order->shipmentInfo)->current_status;
        $current_status_date = optional($order->shipmentInfo)->current_status_date;
        $current_shipment_status = $this->shipmentStatuses[$shipment_status] ?? $shipment_status;

        return $order->orderProducts->map(function ($product) use ($order, $current_shipment_status, $current_status_date) {
            $pickedup_date = '';
            if(!empty($current_shipment_status)){
                $pickedup_date = optional($order->shipmentInfo)->pickedup_date;
            }
            $dispatch_date = optional($order->shipmentInfo)->created_at;
            return [
                $order->vendor_order_number,
                "[" . (new \DateTime($order->channel_order_date))->format('d-m-Y') . "]",
                $order->channel_id,
                strtolower($order->payment_mode) === 'prepaid' ?'Prepaid':'COD',
                $order->b_fullname,
                $order->email,
                $order->b_phone,
                $order->s_complete_address,
                $order->s_zipcode,
                $order->s_city,
                $order->s_state_code,
                $order->s_country_code,
                $order->b_complete_address,
                $order->b_zipcode,
                $order->b_city,
                $order->b_state_code,
                $order->b_country_code,
                $product->sku,
                $product->product_name,
                $product->quantity,
                $product->tax_rate > 0 ? $product->tax_rate : '',
                $product->unit_price,
                optional($order->orderTotals->where('code', 'shipping')->first())->value ?? '',
                optional($order->orderTotals->where('code', 'cod')->first())->value ?? '',
                optional($order->orderTotals->where('code', 'gift_wrap')->first())->value ?? '',
                optional($order->orderTotals->where('code', 'discount')->first())->value ?? '',
                $order->package_length,
                $order->package_breadth,
                $order->package_height,
                $order->package_dead_weight,
                $product->hsn,
                $order->order_tags,
                $order->notes,
                optional($order->channelSetting)->channel_title,
                optional(optional($order->shipmentInfo)->courierSetting)->courier_title,
                optional($order->shipmentInfo)->tracking_id,
                $dispatch_date ? (new \DateTime($dispatch_date))->format('d-m-Y') : '',
                $pickedup_date ? (new \DateTime($pickedup_date))->format('d-m-Y') : '',
                $current_shipment_status,
                $current_status_date ? (new \DateTime($current_status_date))->format('d-m-Y') : '',
                optional($order->shipmentInfo)->pickedup_location_address,
                $order->customer_ip_address,
            ];
        })->toArray();
    }

    public function headings(): array
    {
        return [
            "*Order Id",
            "Order Date [DD-MM-YYYY] (Optional)",
            "*Sales Channel Id",
            "*Payment Method(COD/Prepaid)",
            "*Buyer Full Name",
            "Buyer Email (Optional)",
            "*Buyer Mobile",
            "*Shipping Full Address",
            "*Shipping Address Zipcode(Pincode)",
            "*Shipping Address City",
            "*Shipping Address State",
            "*Shipping Address Country",
            "Billing Full Address",
            "Billing Address Postcode",
            "Billing Address City",
            "Billing Address State",
            "Billing Address Country",
            "*Product SKU",
            "*Product Name",
            "*Product Quantity",
            "Tax %",
            "*Selling Price(Per Unit Item, Inclusive of Tax)",
            "Shipping Charges(Per Order)",
            "COD Charges(Per Order)",
            "Gift Wrap Charges(Per Order)",
            "Total Discount (Per Order)",
            "*Length (cm)",
            "*Breadth (cm)",
            "*Height (cm)",
            "*Weight Of Shipment(kg)",
            "HSN Code",
            "Order tags",
            "Order Notes",
            "Sales Channel Name",
            "Courier Name",
            "Tracking Number",
            "Courier Booking Date",
            "Picked up Date",
            "Current Shipment Status",
            "Current Status Date",
            "Pick up Location Address",
            "Customer IP Address"
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
