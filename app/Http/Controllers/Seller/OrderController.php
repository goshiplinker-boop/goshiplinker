<?php

namespace App\Http\Controllers\Seller;
use App\Http\Controllers\Controller;
use App\Models\ChannelSetting;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Country;
use App\Models\State;
use App\Models\OrderStatus;
use App\Models\OrderProduct;
use App\Models\OrderTotal;
use App\Models\PickupLocation;
use App\Models\Customer;
use App\Models\PaymentMapping;
use App\Models\BuyerInvoiceSetting;
use App\Models\BuyerShippingLabelSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\OrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\OrdersImport;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Exports\OrderExport;
use App\Models\CourierSetting;
use App\Models\ShipmentInfo;
use App\Models\Manifest;
use App\Models\ManifestOrder;
use App\Models\TrackingHistory;
use App\Models\Courier;
use App\Models\ShipmentStatus;
use Picqer\Barcode\BarcodeGeneratorPNG;
use App\Models\ManageTrackingPage;
use Illuminate\Support\Facades\Cache;
use App\Models\OrderLog;
use App\Services\ShopifyGraphQLService;
use App\Models\OrderCourierResponse;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;
use App\Models\SellersCourier;
class OrderController extends Controller
{
    public function index(Request $request)
    {
        $companyId = session("company_id");
        $limit = $request->input('limit', default_pagination_limit());        
       // return $request->all();
        if ($limit <= 0 || $limit > 500) {
            $limit = default_pagination_limit();
        }
        if ($request->has('clear_filters') && $request->get('clear_filters') == 1) {
            $tab = $request->tab ?? "new";
            session()->forget('order_filters_'.$companyId);
            return redirect()->route('order_list', ['tab' => $tab,'limit' => $limit]);
        }      
        // Persist filters in session for POST requests
        if ($request->isMethod('post')) {
            session(['order_filters_'.$companyId => $request->all()]);            
            $tab = $request->tab ?? "new"; 
            $is_filter=0;
            foreach($request->all() as $filter_key=>$filter){
                if($filter_key=='_token' || $filter_key=='tab'){
                    continue;
                }
                if(!empty($filter)){
                    $is_filter = 1;
                }
            }
            if($is_filter==0){
                session()->forget('order_filters_'.$companyId);
            }
            return redirect()->route('order_list', ['tab' => $tab,'limit' => $limit]); // Pass query params correctly
        }
        // Retrieve filters from session or request
        $filters = session('order_filters_'.$companyId, $request->all());
        $tab = $filters['tab'] ?? $request->tab ?? 'new';
        $filters['tab'] = $tab;
        if(isset($request->startDate)){
            $filters['startDate'] = $request->startDate ?? null;
        }else{
            $fromDate = Carbon::now()->subDays(30)->format('Y-m-d H:i:s');
            $filters['startDate'] =$fromDate;
        }
        if(isset($request->endDate)){
            $filters['endDate'] = isset($request->endDate) ? $request->endDate . " 23:59:59" : null;
        }
    
        // Query filters based on tab selection
        $queryFilters = ['o.company_id' => $companyId] + $this->getTabFilters($tab);
        //return $queryFilters;
        // Fetch orders
        //$limit = $request->input('limit', default_pagination_limit()); // fallback to default

        $orders = $this->getOrdersQuery($queryFilters, $tab, $filters)
            ->paginate($limit)
            ->appends(['limit' => $limit])
            ->appends($request->except('page'));  
        // Fetch order counts
        $counts = $this->getOrderCounts($companyId, $filters,$tab);
        $aggrigation_couriers = explode(',', env('AGGRIGATION_COURIERS'));

        $agg_couriers = OrderCourierResponse::whereIn("courier_code", $aggrigation_couriers)
            ->get()
            ->groupBy('courier_code');

        // Fetch supporting data
        $pickup_address = PickupLocation::where("status", 1)->where("company_id", $companyId)->get();
        $defaultPickup = $pickup_address->firstWhere('default', 1)?? $pickup_address->first();
        
        $parent_company_id = session('parent_company_id')??0;
       // $all_couriers = CourierSetting::where("status", 1)->where("company_id", $companyId)->get()->keyBy('courier_id');
     
        $all_couriers = CourierSetting::with('sellersCouriers')
            ->where('status', 1)
            ->where('company_id', $parent_company_id)
            ->whereHas('sellersCouriers', function ($q) use ($companyId) {
                $q->where('seller_courier_status', 1)
                ->where('company_id', $companyId); // seller's company
            })
            ->get()
            ->keyBy('courier_id');
   // return $all_couriers;
        $all_channels = ChannelSetting::where("status", 1)->where("company_id", $companyId)->get()->keyBy('channel_id');
        $statuses = OrderStatus::where("status", 1)->get();
        $shipment_statuses = DB::table('shipment_statuses')
            ->where('status', 1)
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->code => (array) $item];
            })
            ->toArray();
        $manageTracking = ManageTrackingPage::where("status", 1)->where("company_id", $companyId)->first();
        $parent_shipment_statuses = ShipmentStatus::whereColumn('parent_code', 'code')
            ->where('status', '1')
            ->pluck('name','parent_code');
    
        // Payment mappings
        $unmapped_payment_methods = $this->getUnmappedPaymentMethods($companyId);
        // courier mappings
        $unmapped_couriers = $this->getUnmappedCouriers($companyId);
        $customer_order_histories = array(
            "1"=>"1",
            "2"=>"2",
            "3"=>"3",
            "4"=>"Mora than 3",
        );   
        $orderweights = array(
            "0-0.25"=>"0 Grams - 250 Grams",
            "0.25-0.5"=>"250 Grams - 500 Grams",
            "0.5-1"=>"500 Grams - 1 Kilogram",
            "1-2"=>"1 Kilograms - 2 Kilograms",
            "2-5"=>"2 Kilograms - 5 Kilograms",
            "5"=>"More than 5 Kilograms",
        );  
        $shipping_calculate_couriers = explode(',', env('SHIPPING_CALCULATE_COURIERS'));
        $order_filters = session('order_filters_'.$companyId,[]);
        $custom_channel = 0;
        if($all_channels){
            foreach($all_channels as $all_channel){
                if($all_channel['channel_code']=='custom' && $all_channel['status']==1){
                    $custom_channel = 1;
                    break;
                }

            }
            
        }
        return view("seller.orders.index", [
            "orders" => $orders,
            "counts" => $counts,
            "tab" => $tab,
            "manageTracking" => $manageTracking,
            "payment_mappings" => $unmapped_payment_methods,
            "courier_mappings" => $unmapped_couriers,
            "filters" => $filters,
            "all_locations" => $pickup_address,
            "defaultPickup" => $defaultPickup,
            "all_couriers" => $all_couriers,
            "all_channels" => $all_channels,
            "exist_custom_channel" => $custom_channel,
            "statuses"=>$statuses,
            "customer_order_histories"=>$customer_order_histories,
            "orderweights"=>$orderweights, 
            "order_filters"=>$order_filters,
            "shipment_statuses"=>$shipment_statuses,
            "parent_shipment_statuses"=>$parent_shipment_statuses,
            "shipping_calculate_couriers"=>$shipping_calculate_couriers,
            "company_id" => $companyId,
            "agg_couriers" => $agg_couriers,
            "page_size"=>$limit

        ]);
    }
    private function getTabFilters($tab)
    {
        return match ($tab) {
            "new" => ['o.status_code' => ['N']],
            "readytoship" => ['o.status_code' => ['P', 'M']],
            "shipped" => ['o.status_code' => ['S']],
            "int" => ['si.current_status' => 'INT'],
            "del" => ['si.current_status' => 'DEL'],
            default => []
        };
    }
    private function getcsvTabFilters($tab)
    {
        return match ($tab) {
            "new" => ['status_code' => ['N']],
            "readytoship" => ['status_code' => ['P', 'M']],
            "shipped" => ['status_code' => ['S']],
            "int" => ['current_status' => 'INT'],
            "del" => ['current_status' => 'DEL'],
            default => []
        };
    }
    private function getUnmappedPaymentMethods($companyId)
    {
        return DB::table("channel_settings as cs")
            ->join("payment_mappings as pm", function ($join) {
                $join->on("cs.company_id", "=", "pm.company_id")
                    ->on("cs.channel_id", "=", "pm.channel_id");
            })
            ->where("pm.status", 0)
            ->where("cs.company_id", $companyId)
            ->groupBy("cs.company_id", "cs.channel_id", "cs.channel_title", "cs.channel_code")
            ->get();
    }
    private function getUnmappedCouriers($companyId)
    {
        return DB::table("channel_settings as cs")
            ->join("courier_mappings as cm", function ($join) {
                $join->on("cs.company_id", "=", "cm.company_id")
                    ->on("cs.channel_id", "=", "cm.channel_id");
            })
            ->where("cm.status", 0)
            ->where("cs.company_id", $companyId)
            ->groupBy("cs.company_id", "cs.channel_id", "cs.channel_title", "cs.channel_code")
            ->get();
    }
   
    private function applyFilters($query, $filters, $tab)
    {
        /* Date filters */
        if (!empty($filters['startDate'])) {
            $query->where('o.channel_order_date', '>=', $filters['startDate']);
        }

        if (!empty($filters['endDate'])) {
            $query->where('o.channel_order_date', '<=', $filters['endDate'].' 23:59:59');
        }

        /* Phone number (FIXED grouping) */
        if (!empty($filters['phone_number'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('o.s_phone', $filters['phone_number'])
                ->orWhere('o.b_phone', $filters['phone_number']);
            });
        }

        /* Payment mode */
        if (!empty($filters['payment_mode'])) {
            $query->where(
                'o.payment_mode',
                $filters['payment_mode'] === 'cod' ? '=' : '!=',
                'cod'
            );
        }

        /* Order IDs */
        if (!empty($filters['order_ids'])) {
            $orderIds = is_array($filters['order_ids'])
                ? $filters['order_ids']
                : explode(',', $filters['order_ids']);

            $query->whereIn('o.id', array_map('trim', $orderIds));
        }

        /* Vendor order numbers */
        if (!empty($filters['vendor_order_numbers'])) {
            $query->whereIn(
                'o.vendor_order_number',
                array_map('trim', explode(',', $filters['vendor_order_numbers']))
            );
        }

        /* Channel */
        if (!empty($filters['channel_id'])) {
            $query->where('o.channel_id', $filters['channel_id']);
        }

        /* Courier IDs */
        if (!empty($filters['courier_ids'])) {
            $query->whereIn(
                'si.courier_id',
                array_map('trim', explode(',', $filters['courier_ids']))
            );
        }
        if (isset($filters['label_generated']) && $filters['label_generated'] !== '') {
            $query->where('si.label_generated', (int) $filters['label_generated']);
        }

        /* Aggregator courier (FIXED) */
        if (!empty($filters['agg_courier_name'])) {
            $query->where(function ($q) use ($filters) {
                foreach ($filters['agg_courier_name'] as $code => $name) {
                    if ($name) {
                        $q->orWhere(function ($sq) use ($code, $name) {
                            $sq->where('ocr.courier_code', $code)
                            ->where('ocr.courier_name', $name);
                        });
                    }
                }
            });
        }

        /* Tracking numbers */
        if (!empty($filters['tracking_numbers'])) {
            $query->whereIn(
                'si.tracking_id',
                array_map('trim', explode(',', $filters['tracking_numbers']))
            );
        }

        /* Shipment status */
        if (!empty($filters['shipment_status_code'])) {
            $statusCodes = ShipmentStatus::where('status', 1)
                ->where('parent_code', $filters['shipment_status_code'])
                ->pluck('code')
                ->toArray();

            $query->whereIn('si.current_status', $statusCodes);
        }

        /* Pickup location */
        if (!empty($filters['pickup_location_id'])) {
            $query->where('si.pickedup_location_id', $filters['pickup_location_id']);
        }

        /* Order status */
        if (!empty($filters['order_status_codes'])) {
            $query->whereIn(
                'o.status_code',
                array_map('trim', $filters['order_status_codes'])
            );
        }

        /* SKU */
        if (!empty($filters['sku'])) {
            $query->where('op.sku', 'LIKE', "%{$filters['sku']}%");
        }

        /* Order tags */
        if (!empty($filters['order_tags'])) {
            $query->whereIn(
                'o.order_tags',
                array_map('trim', explode(',', $filters['order_tags']))
            );
        }

        /* Weight range */
        if (!empty($filters['order_weight'])) {
            [$min, $max] = array_pad(explode('-', $filters['order_weight']), 2, null);

            $query->where('o.package_dead_weight', '>=', $min);

            if ($max !== null) {
                $query->where('o.package_dead_weight', '<=', $max);
            }
        }

        /* Order history (OPTIMIZED) */
        if (!empty($filters['order_history'])) {
            $query->whereHas('customer.orders', function ($q) use ($filters) {
                $filters['order_history'] <= 3
                    ? $q->havingRaw('COUNT(id) = ?', [$filters['order_history']])
                    : $q->havingRaw('COUNT(id) >= ?', [$filters['order_history']]);
            });
        }

        return $query;
    }
    private function getOrdersQuery($queryFilters, $tab, $filters)
    {
        $orders = DB::table('orders as o')
            ->select(array_merge(
                [
                    'o.*', 'cs.channel_title','cs.brand_logo', 'cs.channel_code','si.tracking_id', 'si.courier_id',
                    'si.current_status', 'si.fulfillment_status', 'si.current_status_date',
                    'si.origin', 'si.destination', 'si.pickedup_location_id',
                    'si.pickedup_location_address', 'si.manifest_created', 'si.pickedup_date','si.label_generated',
                    'op.sku', 'op.quantity', 'op.product_name',
                    'cos.courier_title', 'cos.courier_code','ocr.courier_name as agrigation_coutier_name',
                    DB::raw('(SELECT status_name FROM order_statuses WHERE status_code = o.status_code) as status_name'),
                ],
                $tab === 'manifested' ? [
                    'm.id as manifest_id',
                    DB::raw("COUNT(DISTINCT mo.order_id) as manifest_ordercount"),
                    'm.pickup_created',
                    'm.created_at as manifest_created_at',
                ] : []
            ))
            ->join('order_products as op', 'op.order_id', '=', 'o.id')
            ->join('channel_settings as cs', 'cs.channel_id', '=', 'o.channel_id')
            ->leftJoin('shipment_info as si', 'si.order_id', '=', 'o.id')            
            ->leftJoin('courier_settings as cos', 'si.courier_id', '=', 'cos.courier_id')
            ->leftJoin('order_courier_responses as ocr', 'ocr.order_id', '=', 'si.order_id');

        // Conditional join for "manifested" tab
        if ($tab === 'manifested') {
            $orders->join('manifest_orders as mo', 'mo.order_id', '=', 'o.id')
                ->join('manifests as m', 'm.id', '=', 'mo.manifest_id');
        }

        // Apply filters dynamically
        foreach ($queryFilters as $key => $value) {
            is_array($value) ? $orders->whereIn($key, $value) : $orders->where($key, $value);
        }
        $orders->whereNull('o.deleted_at');

        return $this->applyFilters($orders, $filters, $tab)
            ->groupBy($tab === 'manifested' ? 'm.id' : 'o.id')
            ->orderByDesc($tab === 'manifested' ? 'm.id' : 'o.channel_order_date');
    }

/**
 * Get order counts by status
 */
    private function getOrderCounts($companyId, $filters,$tab)
    {
        $counts = [
            "new" => 0, "readytoship" => 0, "manifested" => 0,"shipped" => 0,
            "int" => 0, "del" => 0, "all" => 0
        ];

        // Count orders by status_code
        $orderCounts = DB::table('orders as o')
            ->select('o.status_code', DB::raw('COUNT(DISTINCT o.id) as order_count'))
            ->join('order_products as op', 'op.order_id', '=', 'o.id')
            ->join('channel_settings as cs', 'cs.channel_id', '=', 'o.channel_id')
            ->leftJoin('shipment_info as si', 'si.order_id', '=', 'o.id')
            ->leftJoin('courier_settings as cos', 'si.courier_id', '=', 'cos.courier_id')
            ->leftJoin('order_courier_responses as ocr', 'ocr.order_id', '=', 'si.order_id')
            ->where("o.company_id", $companyId)
            ->whereNull('o.deleted_at');
            $orderCounts =  $this->applyFilters($orderCounts, $filters,$tab)
            ->groupBy('o.status_code')
            ->get();

        foreach ($orderCounts as $orderCount) {
            $counts['all'] += $orderCount->order_count;
            if ($orderCount->status_code == 'N') $counts['new'] = $orderCount->order_count;
            if (in_array($orderCount->status_code, ['P', 'M'])) $counts['readytoship'] += $orderCount->order_count;
            if ($orderCount->status_code == 'S') $counts['shipped'] = $orderCount->order_count;
        }

        // Count orders by shipment status
        $shipmentCounts = DB::table('orders as o')
            ->select('si.current_status', DB::raw('COUNT(DISTINCT o.id) as order_count'))
            ->join('order_products as op', 'op.order_id', '=', 'o.id')
            ->leftJoin('shipment_info as si', 'si.order_id', '=', 'o.id')
            ->leftJoin('order_courier_responses as ocr', 'ocr.order_id', '=', 'si.order_id')
            ->where('o.company_id', $companyId)
            ->whereIn('si.current_status', ['INT', 'DEL'])
            ->whereNull('o.deleted_at');
            $shipmentCounts =  $this->applyFilters($shipmentCounts, $filters,$tab)
            ->groupBy('si.current_status')
            ->get();

        foreach ($shipmentCounts as $orderCount) {
            $counts[strtolower($orderCount->current_status)] = $orderCount->order_count;
        }

        // Count manifested orders
        $manifested = DB::table('orders as o')
            ->join('order_products as op', 'op.order_id', '=', 'o.id')
            ->leftJoin('shipment_info as si', 'si.order_id', '=', 'o.id')
            ->leftJoin('order_courier_responses as ocr', 'ocr.order_id', '=', 'si.order_id')
            ->join('manifest_orders as mo', 'mo.order_id', '=', 'o.id')
            ->join('manifests as m', 'm.id', '=', 'mo.manifest_id')
            ->where('o.company_id', $companyId)
            ->whereNull('o.deleted_at');
            $counts['manifested'] =  $this->applyFilters($manifested, $filters,$tab)
            ->count(DB::raw('DISTINCT m.id'));

        return $counts;
    }
    public function create()
    {
        // Return a view with a form to create a new order
        $company_id = session('company_id');
        $countries = Country::all();
        $channel_id = ChannelSetting::where('company_id', $company_id)
            ->where('status', 1)
            ->where('channel_code', 'custom')
            ->value('channel_id');

        return view("seller.orders.form",['channel_id'=>$channel_id,'countries'=>$countries]);
    }
    public function addOrder(Request $request, OrderService $orderService)
    {//return $request->all();
        $rules = [
            'vendor_order_number' => 'required|string|max:191',
            'channel_id' => 'required|integer|exists:channels,id',
            'payment_mode' => 'required|string|in:prepaid,cod',
            'currency_code' => 'required|string|min:3|max:3',

            // customer / contact
            'fullname' => 'required|string|max:191',
            'email' => 'nullable|email|max:191',
            'phone_number' => 'nullable|string|min:10|max:10',

            // shipping fields
            's_fullname' => 'required|string|max:191',
            's_company' => 'nullable|string|max:191',
            's_complete_address' => 'required|string',
            's_landmark' => 'nullable|string|max:255',
            's_phone' => 'required|string|max:10',
            's_zipcode' => 'nullable|string|max:6',
            's_city' => 'nullable|string|max:100',
            's_state_code' => 'nullable|string|min:2|max:2',
            's_country_code' => 'nullable|string|min:2|max:2',

            // billing fields (can be nullable if "same as shipping" used)
            'b_fullname' => 'nullable|string|max:191',
            'b_company' => 'nullable|string|max:191',
            'b_complete_address' => 'nullable|string',
            'b_landmark' => 'nullable|string|max:255',
            'b_phone' => 'nullable|string|max:10',
            'b_zipcode' => 'nullable|string|max:6',
            'b_city' => 'nullable|string|max:100',
            'b_state_code' => 'nullable|string|min:2|max:2',
            'b_country_code' => 'nullable|string|min:2|max:2',

            // products array validation - ensure at least one product
            'products' => 'required|array|min:1',
            'products.*.product_name' => 'required|string|max:255',
            'products.*.sku' => 'required|string|max:191',
            'products.*.hsn' => 'nullable',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_price' => 'required|numeric|min:0',
            'products.*.tax_rate' => 'nullable|numeric|min:0',
            'products.*.tax_amount' => 'nullable|numeric|min:0',
            'products.*.total_price' => 'required|numeric|min:0',

            // package fields (optional)
            'package_dead_weight' => 'nullable|numeric|min:0.001',
            'package_length' => 'nullable|numeric|min:1',
            'package_breadth' => 'nullable|numeric|min:1',
            'package_height' => 'nullable|numeric|min:1',

            // totals and charges (hidden fields)
            'sub_total' => 'nullable|numeric|min:0',
            'order_tax_total' => 'nullable|numeric|min:0',
            'shipping_charges' => 'nullable|numeric|min:0',
            'cod_charges' => 'nullable|numeric|min:0',
            'giftwrap' => 'nullable|numeric|min:0',
            'order_discount' => 'nullable|numeric|min:0',
            'order_total' => 'nullable|numeric|min:0',
        ];

        $companyId = session('company_id');

        // validate request
        $order = $request->validate($rules);

        // check if order already exists
        $existorder = $orderService->isOrderAlreadyImported(
            $order['vendor_order_number'],
            $companyId,
            $order['channel_id']
        );
        $order['vendor_order_id'] = $order['vendor_order_number'];
        $order['channel_order_date'] = NOW();
        $order['order_products'] = $order['products'];
        if ($existorder) {
            return back()->withInput()->with('error', 'Order number already exists.');
        }

        try {            
            // normalize totals from validated data (use hidden fields where appropriate)
            $total = isset($order['order_total']) ? (float) $order['order_total'] : 0.0;
            $order_subtotal = isset($order['sub_total']) ? (float) $order['sub_total'] : 0.0;
            $order_tax_total = isset($order['order_tax_total']) ? (float) $order['order_tax_total'] : 0.0;
            $order_discount = isset($order['order_discount']) ? (float) $order['order_discount'] : 0.0;
            $order_shipping_charges = isset($order['shipping_charges']) ? (float) $order['shipping_charges'] : 0.0;
            $cod_charges = isset($order['cod_charges']) ? (float) $order['cod_charges'] : 0.0;
            $giftwrap = isset($order['giftwrap']) ? (float) $order['giftwrap'] : 0.0;

            // build order_totals array
            $order['order_totals'] = [
                ['title' => 'Subtotal', 'code' => 'sub_total', 'value' => $order_subtotal, 'sort_order' => 1],
                ['title' => 'Total', 'code' => 'total', 'value' => $total, 'sort_order' => 9],
            ];

            if ($order_shipping_charges > 0) {
                $order['order_totals'][] = [
                    'title' => 'Shipping',
                    'code' => 'shipping',
                    'value' => $order_shipping_charges,
                    'sort_order' => 2
                ];
            }

            if ($order_tax_total > 0) {
                $order['order_totals'][] = [
                    'title' => 'Tax',
                    'code' => 'tax',
                    'value' => $order_tax_total,
                    'sort_order' => 3
                ];
            }
            if ($giftwrap > 0) {
                $order['order_totals'][] = [
                    "title" => "Giftwrap",
                    "code" => "giftwrap",
                    "value" => $giftwrap,
                    "sort_order" => 5,
                ];
            }
            if ($cod_charges > 0) {
                $order['order_totals'][] = [
                    "title" => "COD Charges",
                    "code" => "cod_charges",
                    "value" => $cod_charges,
                    "sort_order" => 6,
                ];
            }

            if ($order_discount > 0) {
                $order['order_totals'][] = [
                    'title' => 'Discount',
                    'code' => 'discount',
                    'value' => $order_discount,
                    'sort_order' => 4
                ];
            }

            // buyer invoice settings
            $buyer_invoice_settings = BuyerInvoiceSetting::where('company_id', $companyId)->first();
            $invoice_type = $buyer_invoice_settings->number_type ?? 'order_number';
            $invoice_prefix = '';
            $invoice_start_from = '';

            if ($invoice_type === 'custom_number') {
                $invoice_prefix = $buyer_invoice_settings->prefix ?? '';
                $invoice_start_from = $buyer_invoice_settings->start_from ?? '';
            }

            $order['invoice_type'] = $invoice_type;
            $order['invoice_prefix'] = $invoice_prefix;
            $order['invoice_start_from'] = $invoice_start_from;

            // create order via service — expect created order returned
            $createdOrder = $orderService->createOrder($order, $companyId);

            // if service returns created model, redirect to order view
            if ($createdOrder && isset($createdOrder->id)) {
                return redirect()->route('order_view', $createdOrder->id)
                    ->with('success', 'Order has been created successfully!');
            }

            // fallback: success message but no id returned
            return redirect()->route('orders.index')
                ->with('success', 'Order has been created successfully!');
        } catch (\Exception $e) {
             \Log::error($e->getMessage());
            return back()->withInput()->with('error', 'Failed to create order: ' . $e->getMessage());
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, OrderService $orderService)
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        try {
            $validated = $request->validate([
                "vendor_order_id" => "required|string",
                "vendor_order_number" => "required|string",
                "channel_id" => "required|exists:channels,id",
                "channel_order_date" => "required|date",
                "fullname" => "required|string",
                "email" => "required|email",
                "phone_number" => "required|string",
                // Shipping details
                "s_fullname" => "required|string|min:3",
                "s_company" => "nullable|string",
                "s_complete_address" => "required|string|min:10",
                "s_landmark" => "nullable|string",
                "s_phone" => "required|string",
                "s_zipcode" => "required|integer",
                "s_city" => "required|string",
                "s_state_code" => "required|string|min:2|max:2",
                "s_country_code" => "required|string|min:2|max:2",
                // Billing details
                "b_fullname" => "required|string|min:3",
                "b_company" => "nullable|string",
                "b_complete_address" => "required|string|min:10",
                "b_landmark" => "nullable|string",
                "b_phone" => "required|string",
                "b_zipcode" => "required|integer",
                "b_city" => "required|string",
                "b_state_code" => "required|string|min:2|max:2",
                "b_country_code" => "required|string|min:2|max:2",
                "notes" => "nullable|string",
                "package_breadth" => "nullable|numeric",
                "package_height" => "nullable|numeric",
                "package_length" => "nullable|numeric",
                "package_dead_weight" => "nullable|numeric",
                "currency_code" => "required|string",
                "financial_status" => "nullable|string",
                "payment_mode" => "required|string",
                "payment_method" => "required|string",
                "sub_total" => "required|numeric",
                "order_total" => "required|numeric",
                // order_products
                "order_products" => "required|array",
                "order_products.*.product_name" => "required|string",
                "order_products.*.sku" => "required|string",
                "order_products.*.unit_price" => "required|numeric",
                "order_products.*.quantity" => "required|integer",
                "order_products.*.discount" => "nullable|numeric",
                "order_products.*.shipping" => "nullable|numeric",
                "order_products.*.hsn" => "nullable|string",
                "order_products.*.tax_rate" => "nullable|numeric",
                "order_products.*.tax_amount" => "nullable|numeric",
                "order_products.*.total_price" => "required|numeric",
                "order_totals" => "required|array",
                "order_totals.*.title" => "required|string|max:255",
                "order_totals.*.code" => "required|string|max:100",
                "order_totals.*.value" => "required|numeric|min:0",
                "order_totals.*.sort_order" => "required|integer|min:0",
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if (request()->expectsJson() || request()->is("api/*")) {
                return response()->json(
                    [
                        "success" => false,
                        "errors" => $e->validator->errors(),
                    ],
                    422
                ); // Return 422 Unprocessable Entity
            }
        }
        try {
            $buyer_invoice_settings = BuyerInvoiceSetting::where(
                "company_id",
                $companyId
            )->first();
            $invoice_type =
                $buyer_invoice_settings->number_type ?? "order_number";
            $invoice_prefix = "";
            $invoice_start_from = "";
            if ($invoice_type == "custom_number") {
                $invoice_prefix = $buyer_invoice_settings->prefix ?? "";
                $invoice_start_from = $buyer_invoice_settings->start_from ?? "";
            }
            $request["invoice_type"] = $invoice_type;
            $request["invoice_prefix"] = $invoice_prefix;
            $request["invoice_start_from"] = $invoice_start_from;
            $order = $orderService->createOrder($request, $companyId);

            if ($request->wantsJson() || $request->is("api/*")) {
                return response()->json(
                    [
                        "message" => "Order has been created successfully!",
                        "order" => $order,
                    ],
                    201
                );
            } else {
                return redirect()
                    ->route("order_list")
                    ->with("success", "Order has been created successfully!");
            }
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->is("api/*")) {
                return response()->json(
                    ["error" => "Order creation failed!"],
                    500
                );
            } else {
                return redirect()
                    ->back()
                    ->withErrors(["error" => "Order creation failed!"]);
            }
        }
    }

    public function view(string $id)
    {
        $order = Order::with([
            "orderProducts",
            "user",
            "company",
            "channelSetting",
            "orderTotals",
            "customer",
            "shipmentInfo",
            "trackingHistory",
            "courier",
            "status"
        ])
            ->where("company_id", session("company_id"))
            ->findOrFail($id);

        $countries = Country::pluck('country_name', 'country_code'); 
        $states = State::pluck('name', 'state_code');
       
        // Logic to compare the country and city values by matching the country and state codes
        $shippingCountry = $countries[$order->s_country_code]??$order->s_country_code;
        $billingCountry = $countries[$order->b_country_code]??$order->b_country_code;
        
        
        $billingState =  $states[$order->b_state_code]??$order->b_state_code;
        $shippingState =  $states[$order->s_state_code]??$order->s_state_code;
        

        // Assign variables for easier access in the view
        $orderProducts = $order->orderProducts;
        $user = $order->user;
        $company = $order->company;
        $orderTotals = $order->orderTotals;
        $customer = $order->customer;
        $notes = $order->notes;
        $shipmentInfo = $order->shipmentInfo;
        $tags = !empty($order->order_tags)
            ? explode(",", $order->order_tags)
            : [];
        $history = TrackingHistory::where("order_id", $id) // Filter by order_id
            ->latest() // Order by the most recent first
            ->first(); // Get the first (latest) record
        $trackingHistory = $order->trackingHistory;
        $courier = $shipmentInfo
            ? Courier::find($shipmentInfo->courier_id)
            : null;

        // In your controller, sort the tracking history by the created_at date in descending order
        $trackingHistory = $trackingHistory->sortByDesc(function ($history) {
            return $history->current_shipment_status_date;
        });

        // Group by date after sorting
        $groupedTrackingHistory = $trackingHistory->groupBy(function (
            $history
        ) {
            // Ensure the current_shipment_status_date is a Carbon instance and group by the date only (Y-m-d)
            return Carbon::parse(
                $history->current_shipment_status_date
            )->toDateString();
        });
        $ordersCount = Order::where(
            "customer_id",
            $order->customer_id
        )->count();

        // Return the edit view with the required data and order count
        return view(
            "seller.orders.view",
            compact(
                "order",
                "orderProducts",
                "user",
                "company",
                "orderTotals",
                "customer",
                "tags",
                "notes",
                "shippingCountry",
                "billingCountry",
                "billingState",
                "shippingState",
                "ordersCount",
                "shipmentInfo",
                "trackingHistory",
                "courier",
                "groupedTrackingHistory",
                "history" // Pass the order count to the view
            )
        );
    }

    public function edit($id)
    {
        // Fetch the order by its ID
        $order = Order::with(
            "orderProducts",
            "user",
            "company",
            "orderTotals",
            "customer"
        )
            ->where("company_id", session("company_id"))
            ->findOrFail($id);
        $states = State::all(); // Fetch all states

        return view("seller.orders.edit", compact("order", "states"));
        // Return the view and pass the order data
    }
    public function editOrder($id)
    {
        $company_id = session('company_id');
        $order = Order::with(['orderProducts', 'orderTotals'])->where('company_id', $company_id)->findOrFail($id);
        $countries = Country::all();
        $channel_id = $order->channel_id;
        $orderTotals = $order->orderTotals->pluck('value','code');
        return view('seller.orders.form', [
            'order' => $order,
            'order_totals' => $orderTotals,
            'countries' => $countries,
            'channel_id' => $channel_id,
            'is_edit' => true
        ]);
    }
    public function updateOrder(Request $request, OrderService $orderService, $id)
    {
        $company_id = session('company_id');
        $rules = [
                'vendor_order_number' => 'required|string|max:191',
                'channel_id' => 'required|integer|exists:channels,id',
                'currency_code' => 'required|string|min:3|max:3',

                // customer / contact
                'fullname' => 'required|string|max:191',
                'email' => 'nullable|email|max:191',
                'phone_number' => 'nullable|string|min:10|max:10',

                // shipping fields
                's_fullname' => 'required|string|max:191',
                's_company' => 'nullable|string|max:191',
                's_complete_address' => 'required|string',
                's_landmark' => 'nullable|string|max:255',
                's_phone' => 'required|string|max:10',
                's_zipcode' => 'nullable|string|max:6',
                's_city' => 'nullable|string|max:100',
                's_state_code' => 'nullable|string|min:2|max:2',
                's_country_code' => 'nullable|string|min:2|max:2',

                // billing fields (can be nullable if "same as shipping" used)
                'b_fullname' => 'nullable|string|max:191',
                'b_company' => 'nullable|string|max:191',
                'b_complete_address' => 'nullable|string',
                'b_landmark' => 'nullable|string|max:255',
                'b_phone' => 'nullable|string|max:10',
                'b_zipcode' => 'nullable|string|max:6',
                'b_city' => 'nullable|string|max:100',
                'b_state_code' => 'nullable|string|min:2|max:2',
                'b_country_code' => 'nullable|string|min:2|max:2',

                // products array validation - ensure at least one product
                'products' => 'required|array|min:1',
                'products.*.order_product_id' => 'nullable|integer',
                'products.*.product_name' => 'required|string|max:255',
                'products.*.sku' => 'nullable|string|max:191',
                'products.*.hsn' => 'nullable',
                'products.*.quantity' => 'required|integer|min:1',
                'products.*.unit_price' => 'required|numeric|min:0',
                'products.*.total_price' => 'required|numeric|min:0',

                // package fields (optional)
                'package_dead_weight' => 'nullable|numeric|min:0.001',
                'package_length' => 'nullable|numeric|min:1',
                'package_breadth' => 'nullable|numeric|min:1',
                'package_height' => 'nullable|numeric|min:1',

                // totals and charges (hidden fields)
                'sub_total' => 'nullable|numeric|min:0',
                'order_tax_total' => 'nullable|numeric|min:0',
                'shipping_charges' => 'nullable|numeric|min:0',
                'cod_charges' => 'nullable|numeric|min:0',
                'giftwrap' => 'nullable|numeric|min:0',
                'order_discount' => 'nullable|numeric|min:0',
                'order_total' => 'nullable|numeric|min:0',
            ];
        $order = $request->validate($rules);
        try {
            $total = isset($order['order_total']) ? (float) $order['order_total'] : 0.0;
                $order_subtotal = isset($order['sub_total']) ? (float) $order['sub_total'] : 0.0;
                $order_tax_total = isset($order['order_tax_total']) ? (float) $order['order_tax_total'] : 0.0;
                $order_discount = isset($order['order_discount']) ? (float) $order['order_discount'] : 0.0;
                $order_shipping_charges = isset($order['shipping_charges']) ? (float) $order['shipping_charges'] : 0.0;
                $cod_charges = isset($order['cod_charges']) ? (float) $order['cod_charges'] : 0.0;
                $giftwrap = isset($order['giftwrap']) ? (float) $order['giftwrap'] : 0.0;

                // build order_totals array
                $order['order_totals'] = [
                    ['title' => 'Subtotal', 'code' => 'sub_total', 'value' => $order_subtotal, 'sort_order' => 1],
                    ['title' => 'Total', 'code' => 'total', 'value' => $total, 'sort_order' => 9],
                ];

                if ($order_shipping_charges > 0) {
                    $order['order_totals'][] = [
                        'title' => 'Shipping',
                        'code' => 'shipping',
                        'value' => $order_shipping_charges,
                        'sort_order' => 2
                    ];
                }

                if ($order_tax_total > 0) {
                    $order['order_totals'][] = [
                        'title' => 'Tax',
                        'code' => 'tax',
                        'value' => $order_tax_total,
                        'sort_order' => 3
                    ];
                }
                if ($giftwrap > 0) {
                    $order['order_totals'][] = [
                        "title" => "Giftwrap",
                        "code" => "giftwrap",
                        "value" => $giftwrap,
                        "sort_order" => 5,
                    ];
                }
                if ($cod_charges > 0) {
                    $order['order_totals'][] = [
                        "title" => "COD Charges",
                        "code" => "cod_charges",
                        "value" => $cod_charges,
                        "sort_order" => 6,
                    ];
                }

                if ($order_discount > 0) {
                    $order['order_totals'][] = [
                        'title' => 'Discount',
                        'code' => 'discount',
                        'value' => $order_discount,
                        'sort_order' => 4
                    ];
                }
            $orderService->updateOrder($id, $order, $company_id);
            return redirect()->route('order_view', $id)
                ->with('success', 'Order has been updated successfully!');
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return back()->withInput()->with('error', 'Failed to update order: ' . $e->getMessage());
        }
    }
    public function update(Request $request, $id)
    {
        // Find the order by ID
        $order = Order::findOrFail($id);

        try {
            // Validate phone number
            if ($request->has("phone_number")) {
                $request->validate([
                    "phone_number" => "required|digits:10",
                ]);
            }

            // Validate shipping address
            if (
                $request->has("s_complete_address") ||
                $request->has("s_phone") ||
                $request->has("s_city") ||
                $request->has("s_state_code") ||
                $request->has("s_zipcode") ||
                $request->has("s_country_code")
            ) {
                $request->validate([
                    "s_complete_address" => "required|string|max:255",
                    "s_phone" => "required|digits:10",
                    "s_city" => "required|string|max:100",
                    "s_state_code" => "required|string|max:10",
                    "s_landmark" => "required|string",
                    "s_zipcode" => "required|digits:6",
                ]);
            }

            // Validate billing address
            if (
                $request->has("b_complete_address") ||
                $request->has("b_phone") ||
                $request->has("b_city") ||
                $request->has("b_state_code") ||
                $request->has("b_landmark") ||
                $request->has("b_zipcode") ||
                $request->has("b_country_code")
            ) {
                $request->validate([
                    "b_complete_address" => "required|string|max:255",
                    'b_phone' => "required|digits:10",
                    "b_city" => "required|string|max:100",
                    "b_state_code" => "required|string|max:10",
                    "b_landmark" => "required|string",
                    "b_zipcode" => "required|digits:6",
                ]);
            }

            // Update fields
            if ($request->has("phone_number")) {
                $order->phone_number = $request->input("phone_number");
            }

            if ($request->has("s_complete_address")) {
                $order->s_complete_address = $request->input(
                    "s_complete_address"
                );
                $order->s_phone = $request->input("s_phone");
                $order->s_city = $request->input("s_city");
                $order->s_state_code = $request->input("s_state_code");
                $order->s_landmark = $request->input("s_landmark");
                $order->s_zipcode = $request->input("s_zipcode");
            }

            if ($request->has("b_complete_address")) {
                $order->b_complete_address = $request->input(
                    "b_complete_address"
                );
                $order->b_phone = $request->input("b_phone");
                $order->b_city = $request->input("b_city");
                $order->b_state_code = $request->input("b_state_code");
                $order->b_landmark = $request->input("b_landmark");
                $order->b_zipcode = $request->input("b_zipcode");
            }

            // Save the updated order
            $order->save();

            // Redirect on success
            return redirect()
                ->route("order_view", ["id" => $order->id])
                ->with("success", "Order updated successfully!");
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Redirect with validation errors
            return redirect()
                ->route("order_view", ["id" => $id])
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            // Handle other exceptions
            return redirect()
                ->route("order_view", ["id" => $id])
                ->with("error", "An error occurred: " . $e->getMessage());
        }
    }

    public function invoice(Request $request)
    {
        $companyId = session("company_id");
        $paper_width = 287; // 4 inches
        $paper_height = 431; // 6 inches
        $order_ids = explode(",", $request->query("order_ids", ""));
        if (empty($order_ids) || !is_array($order_ids)) {
            abort(400, "Invalid or missing order IDs.");
        }

        $orders = Order::with([
            "orderProducts",
            "channelSetting",
            "company",
            "orderTotals",
        ])
            ->whereIn("id", $order_ids)
            ->get();
        // Check if orders exist
        if ($orders->isEmpty()) {
            abort(404, "No orders found.");
        }
        $html = "";
        foreach ($orders as $order) {
            $logo = $order->channelSetting->brand_logo??null;
            
            $signature = $order->company->brand_logo??null;
            if(!is_file(public_path('assets/images/companies/logo/' . $signature))){
                $signature = '';
            } 
            
            if(!is_file(public_path('assets/images/channels/logos/' . $logo))){
                $logo = 'PM_Logo.png';
            }            
            $order->channel_order_date = Carbon::parse(
                $order->channel_order_date
            )->format("d-m-Y");
            $invoice_date = Carbon::parse($order->created_at)->format("d-m-Y");
            $data = [
                "title" => "4x6 Tax Invoice Label",
                "logo" => $logo,
                "signature" => $signature,
                "invoice_date" => $invoice_date,
                "orders" => $order,
            ];
            $html .= view("seller.orders.invoice_label4x6", $data)->render();
        }

        $pdf = $pdf = Pdf::loadHTML($html)->setPaper([
            0,
            0,
            $paper_width,
            $paper_height,
        ]);
        return $pdf->download(
            "Tax_Invoice_" . $companyId . "_" . time() . ".pdf"
        );
    }
    public function shippingLabel(Request $request)
    {
        $paper_width = 287;   // 4 inch
        $paper_height = 431;  // 6 inch

        $order_ids = array_filter(explode(",", trim($request->query("order_ids", ""))));
        if (empty($order_ids)) {
            abort(400, "Invalid or missing order IDs.");
        }

        $orders = Order::with([
            "orderProducts",
            "orderTotals",
            "company",
            "channelSetting",
            "shipmentInfo",
            "courierResponse"
        ])
        ->whereIn("id", $order_ids)
        ->get();

        if ($orders->isEmpty()) {
            abort(404, "No orders found.");
        }
        $barcode = new BarcodeGeneratorPNG();
        $labelTasks = [];
        foreach ($orders as $order) {
            $courierId = $order->shipmentInfo->courier_id ?? null;
            if(empty($courierId)){
                continue;
            }
            $courier_details = [];
            $courierTitle = $order->courierResponse->courier_name??'';
            $courier_shipping_label = 0;
            if ($courierId) {
                $courier = CourierSetting::where("courier_id", $courierId)->first();
                $courierTitle = !empty($courierTitle) ? $courierTitle : ($courier->courier_title ?? "");
                $courier_details = json_decode($courier->courier_details ?? '{}', true);
                $courier_shipping_label = $courier_details['courier_shipping_label'] ?? 0;
            }
            $courier_location_code='';
            $courierResponse = $order->courierResponse->response ?? [];
            $label_url = $courierResponse['label_url']??'';
            if ($courier_shipping_label==1 && !empty($label_url)) {
                $labelTasks[] = [
                    "type" => "pdf_url",
                    "url"  => $label_url
                ];
                continue;       
            }
            $packages=[];
            if($courierResponse){ 
                $destination_area = $courierResponse['destination_area']??'';
                $courier_location_code = $destination_area;
                $destination_location = $courierResponse['destination_location']??'';
                $courier_location_code .=($destination_location)?'/'.$destination_location:'';
                $cluster_code = $courierResponse['cluster_code']??'';
                $courier_location_code .=($cluster_code)?'/'.$cluster_code:'';
                $packages = $courierResponse['pieces']??[];
            }   
            /* -----------------------------------------------------
                2. Generate HTML for internal label (with barcodes)
            ------------------------------------------------------*/
            $trackingId = $order->shipmentInfo->tracking_id ?? "N/A";
            $mainBarcode = "data:image/png;base64," . base64_encode(
                $barcode->getBarcode($trackingId, $barcode::TYPE_CODE_128, 2, 50)
            );

            /* Child packets */
            $childBarcodes = [];
            $packages = $courierResponse['pieces'] ?? [];

            foreach ($packages as $pkg) {
                $childBarcodes[] = [
                    "id" => $pkg['reference_number'],
                    "barcode" => "data:image/png;base64," . base64_encode(
                        $barcode->getBarcode($pkg['reference_number'], $barcode::TYPE_CODE_128, 2, 50)
                    )
                ];
            }

            /* Order ID barcode (if enabled) */
            $company_id = session('company_id') ?? 0;
            $buyerSettings = BuyerShippingLabelSetting::where('company_id', $company_id)->first();
            $extra_details = json_decode($buyerSettings->extra_details ?? '{}', true);

            $order_id_barcode = "";
            if (!empty($extra_details['enable_order_id_barcode'])) {
                $order_id_barcode = "data:image/png;base64," . base64_encode(
                    $barcode->getBarcode($order->vendor_order_number, $barcode::TYPE_CODE_128, 1, 50)
                );
            }

            $order->channel_order_date = Carbon::parse($order->channel_order_date)->format("d-m-Y");

            // Get branding logo
            $logo = $order->channelSetting->brand_logo ?? $order->company->brand_logo ?? 'PM_Logo.png';
            if (!is_file(public_path('assets/images/channels/logos/' . $logo))) {
                $logo = 'PM_Logo.png';
            }

            /* -----------------------------------------------------
                3. Render HTML using blade
            ------------------------------------------------------*/
            $data = [
                    "title" => "Shipping Label",
                    "company_id" => session('company_id')??0,
                    "courier_location_code"=>$courier_location_code,
                    'order_id_barcode' => $order_id_barcode,
                    "tracking_id_barcode" => $mainBarcode,
                    "child_tracking_id_barcode" =>'',
                    "child_tracking_id" =>'',
                    "logo" => $logo,
                    "notes" => $order->notes,
                    "orders" => $order,
                    "courierTitle" => $courierTitle,
                    "trackingId" => $trackingId,
                    "settings" => $extra_details,
            ];
            $labelHtml = ""; 
            if (!empty($childBarcodes)) {
                foreach ($childBarcodes as $index => $c) {
                    $data['child_tracking_id'] = $c['id'];
                    $data['child_tracking_id_barcode'] = $c['barcode'];
                    $data['package_count'] = ($index + 1) . '/' . count($childBarcodes);

                    $labelHtml = view("seller.orders.mps_shipping_label4x6", $data)->render();
                }
            } else {
                $labelHtml = view("seller.orders.shipping_label4x6", $data)->render();
            }
            $labelTasks[] = [
                "type" => "html",
                "html" => $labelHtml
            ];
        }
        if(empty($labelTasks)){
            return back()->withInput()->with('error', 'Label does not exist with selected orders');
        }
        /* ---------------------------------------------------------
        STEP 4 — Create main PDF from HTML
        ----------------------------------------------------------*/
        $finalPdf = new Fpdi();
        $tempFiles = [];

        // Process tasks in order
        foreach ($labelTasks as $task) {

            if ($task["type"] === "html") {

                $dompdfPdf = Pdf::loadHTML($task["html"])
                    ->setPaper([0, 0, $paper_width, $paper_height]);

                $tmp = storage_path("app/tmp_html_" . uniqid() . ".pdf");
                file_put_contents($tmp, $dompdfPdf->output());
                $tempFiles[] = $tmp;

                // Validate before importing
                if (!self::isValidPdf($tmp)) {
                    logger("INVALID HTML PDF SKIPPED: $tmp");
                    continue;
                }

                self::safeImportPdfInto($finalPdf, $tmp);
            }elseif ($task["type"] === "pdf_url") {

                $content = @file_get_contents($task["url"]);
                if (!$content) {
                    logger("EMPTY PDF FROM URL — SKIPPED: " . $task["url"]);
                    continue;
                }

                $tmp = storage_path("app/tmp_url_" . uniqid() . ".pdf");
                file_put_contents($tmp, $content);
                $tempFiles[] = $tmp;

                // Validate before importing
                if (!self::isValidPdf($tmp)) {
                    logger("INVALID URL PDF SKIPPED: $tmp");
                    continue;
                }

                self::safeImportPdfInto($finalPdf, $tmp);
            }
        }

        /* ---------------------------------------------------------
            STEP 6 — Send final merged PDF to browser
        ----------------------------------------------------------*/
        $output = $finalPdf->Output('S');

        // Cleanup
        foreach ($tempFiles as $file) @unlink($file);

        return response($output, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Shipping_Labels_' . time() . '.pdf"',
        ]);
    }

    private static function isValidPdf($filePath)
    {
        if (!file_exists($filePath)) return false;

        $header = file_get_contents($filePath, false, null, 0, 20);

        if (strpos($header, '%PDF') !== 0) {
            return false;
        }

        return true;
    }
    private static function safeImportPdfInto(Fpdi $fpdi, $filePath)
    {
        try {
            $pageCount = $fpdi->setSourceFile($filePath);
        } catch (\Throwable $e) {
            logger("FPDI FAILED setSourceFile FOR $filePath | " . $e->getMessage());
            return;
        }

        logger("PAGE COUNT = $pageCount");

        for ($i = 1; $i <= $pageCount; $i++) {
            try {
                $tpl = $fpdi->importPage($i);
                $size = $fpdi->getTemplateSize($tpl);

                $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $fpdi->useTemplate($tpl);
            } catch (\Throwable $e) {
                logger("FAILED IMPORT PAGE $i FROM $filePath | " . $e->getMessage());
                continue;
            }
        }
    }   

    public function combinedInvoiceLabel(Request $request)
    {   $company_id = session('company_id')??0;
        $paper_width = 287; // 4 inches
        $paper_height = 431; // 6 inches
        $order_ids = explode(",", $request->query("order_ids", ""));
        if (empty($order_ids) || !is_array($order_ids)) {
            abort(400, "Invalid or missing order IDs.");
        }

        $orders = Order::with([
            "orderProducts",
            "orderTotals",
            "company",
            "shipmentInfo",
            "courierResponse",
            "channelSetting",
        ])
            ->whereIn("id", $order_ids)
            ->get();

        // Check if orders exist
        if ($orders->isEmpty()) {
            abort(404, "No orders found.");
        }
        $generator = new BarcodeGeneratorPNG();
        $combinedHtml = "";
        $packages=[];
        foreach ($orders as $order) {
            $courierResponse = $order->courierResponse->response??[];
            $courier_location_code='';
            if($courierResponse){            
                $destination_area = $courierResponse['destination_area']??'';
                $courier_location_code = $destination_area;
                $destination_location = $courierResponse['destination_location']??'';
                $courier_location_code .=($destination_location)?'/'.$destination_location:'';
                $cluster_code = $courierResponse['cluster_code']??'';
                $courier_location_code .=($cluster_code)?'/'.$cluster_code:'';
                $packages = $courierResponse['pieces']??[];
            }      
            $shipmentInfo = $order->shipmentInfo;
            $trackingId = $shipmentInfo->tracking_id ?? "N/A";
            $courierId = $shipmentInfo->courier_id ?? null;
            $logo = $order->channelSetting->brand_logo;
            $signature = $order->company->brand_logo??null;
            if(!is_file(public_path('assets/images/companies/logo/' . $signature))){
                $signature = '';
            } 
            if(!is_file(public_path('assets/images/channels/logos/' . $logo))){
                $logo = 'PM_Logo.png';
            }  
            //return $path;
            $courierTitle =$order->courierResponse->courier_name??'';
            if ($courierId && empty($courierTitle)) {
                $courier = CourierSetting::where(
                    "courier_id",
                    $courierId
                )->first();
                $courierTitle = $courier->courier_title ?? "";
            }
            $child_tracking_id_barcodes = [];
            $total_package = 0;
            if($packages){
                foreach($packages as $package){
                    $total_package++;
                    $child_tracking_id_barcode = $generator->getBarcode(
                        $package['reference_number'],
                        $generator::TYPE_CODE_128,
                        2,
                        50
                    );
                    $child_tracking_id_barcodes[$package['reference_number']] = "data:image/png;base64," . base64_encode($child_tracking_id_barcode);

                }

            }
            $buyerShippingSettings = BuyerShippingLabelSetting::where('company_id', $company_id)->first();
            $extra_details = json_decode($buyerShippingSettings->extra_details ?? '{}', true);
            $order_id_barcode = '';
            if(isset($extra_details['enable_order_id_barcode']) && !empty($extra_details['enable_order_id_barcode'])){
                // No need of order number barcode as of now
                $order_id_barcode = $generator->getBarcode($order->vendor_order_number, $generator::TYPE_CODE_128,1,50); // You can change the type here
                $order_id_barcode = 'data:image/png;base64,' . base64_encode($order_id_barcode);
            } 
            $tracking_id_barcode = $generator->getBarcode(
                $trackingId,
                $generator::TYPE_CODE_128,
                2,
                50
            ); // You can change the type here

            // Encode the barcode as a base64 image
            $tracking_id_barcode =
                "data:image/png;base64," . base64_encode($tracking_id_barcode);
            $order->channel_order_date = Carbon::parse(
                $order->channel_order_date
            )->format("d-m-Y");
            $invoice_date = Carbon::parse($order->created_at)->format("d-m-Y");
            $data = [
                "title" => "ShippingInvoice Label",
                "logo" => $logo,
                "signature" => $signature,
                "company_id" => session('company_id')??0,
                "invoice_date" => $invoice_date,
                "courier_location_code"=>$courier_location_code,
                "order_id_barcode" => $order_id_barcode,
                "tracking_id_barcode" => $tracking_id_barcode,
                "child_tracking_id_barcode" =>'',
                "child_tracking_id" =>'',
                "notes" => $order->notes,
                "orders" => $order,
                "courierTitle" => $courierTitle,
                "trackingId" => $trackingId,
                "settings" => $extra_details,
            ];
            $invoiceHtml = view("seller.orders.invoice_label4x6", $data)->render();
            $shippingLabelHtml ='';
            if($child_tracking_id_barcodes){
                $package_no=0;
                foreach($child_tracking_id_barcodes as $child_tracking_id=>$child_tracking_id_barcode){
                    $package_no++;
                    $data['child_tracking_id_barcode'] = $child_tracking_id_barcode;
                    $data['child_tracking_id'] = $child_tracking_id;    
                    $data['package_count'] = $package_no.'/'.$total_package;                  
                    $shippingLabelHtml .= view("seller.orders.mps_shipping_label4x6", $data)->render();
                }

            }else{
                $shippingLabelHtml = view(
                    "seller.orders.shipping_label4x6",
                    $data
                )->render();
            }
            $combinedHtml .= $invoiceHtml . $shippingLabelHtml;
           
        }

        $pdf = Pdf::loadHTML($combinedHtml)->setPaper([
            0,
            0,
            $paper_width,
            $paper_height,
        ]);
        return $pdf->download("labelInvoice_" . time() . ".pdf");
    }

    public function addOrders()
    {
        $states = State::select('name', 'state_code')
            ->where('country_code', 'IN')
            ->get();
        return view("seller.orders.bulk_orders" ,compact('states'));
    }
    
    private function validateRow(array $rowData)
    {
        // Initialize an array to store any validation errors
        $error = [];

        // Validate required fields
        if (empty($rowData["*Order Id"])) {
            $error[] = "*Order Id is required";
        }
        if (empty($rowData["*Sales Channel Id"])) {
            $error[] = "Sales Channel Id is required";
        } else {
            $channelId = channelSetting::where(
                "company_id",
                session("company_id")
            )
                ->where("channel_id", $rowData["*Sales Channel Id"])
                ->exists();
            if (empty($channelId)) {
                $error[] = "Sales Channel Id is not valid";
            }
        }
        if (empty($rowData["*Payment Method(COD/Prepaid)"])) {
            $error[] = "*Payment Method(COD/Prepaid) is required";
        }
        if (empty($rowData["*Buyer Full Name"])) {
            $error[] = "*Buyer Full Name is required";
        }
        if (empty($rowData["*Buyer Mobile"])) {
            $error[] = "*Buyer Mobile is required";
        }
        if (empty($rowData["*Shipping Full Address"])) {
            $error[] = "*Shipping Full Address is required";
        }
        if (empty($rowData["*Shipping Address Zipcode(Pincode)"])) {
            $error[] = "*Shipping Address Zipcode(Pincode) is required";
        }
        if (empty($rowData["*Shipping Address City"])) {
            $error[] = "*Shipping Address City is required";
        }
        if (empty($rowData["*Shipping Address State"])) {
            $error[] = "*Shipping Address State is required";
        }
        if (empty($rowData["*Product SKU"])) {
            $error[] = "*Product SKU is required";
        }
        if (empty($rowData["*Product Name"])) {
            $error[] = "*Product Name is required";
        }
        if (empty($rowData["*Product Quantity"])) {
            $error[] = "*Product Quantity is required";
        }
        if (
            empty($rowData["*Selling Price(Per Unit Item, Inclusive of Tax)"])
        ) {
            $error[] =
                "*Selling Price(Per Unit Item, Inclusive of Tax) is required";
        }
        if (
            empty($rowData["*Length (cm)"]) ||
            empty($rowData["*Breadth (cm)"])
        ) {
            $error[] = "*Length (cm) and *Breadth (cm) are required";
        }
        if (empty($rowData["*Height (cm)"])) {
            $error[] = "*Height (cm) is required";
        }
        if (empty($rowData["*Weight Of Shipment(kg)"])) {
            $error[] = "*Weight Of Shipment(kg) is required";
        }

        // Validate *Buyer Mobile (must be numeric and 10 digits long)
        if (
            !is_numeric($rowData["*Buyer Mobile"]) ||
            strlen((string) $rowData["*Buyer Mobile"]) !== 10
        ) {
            $error[] =
                "Buyer Mobile should be numeric and exactly 10 digits without any country dialing code";
        }

        // Validate *Buyer Email if provided
        if (
            !empty($rowData["*Buyer Email"]) &&
            !filter_var($rowData["*Buyer Email"], FILTER_VALIDATE_EMAIL)
        ) {
            $error[] = "Buyer Email (Optional) should be a valid email address";
        }

        
        if (strlen($rowData["*Buyer Full Name"]) < 3 || strlen($rowData["*Buyer Full Name"]) > 50
        ) {
            $error[] =
                "*Buyer Full Name is invalid. It should contain only letters, spaces, hyphens, apostrophes, or dots, and be between 3 and 50 characters.";
        }

        // Validate *Order Id (alphanumeric, 3-20 characters long)
        $orderId = trim((string) ($rowData["*Order Id"] ?? ''));
        if (strlen($orderId) < 3 || strlen($orderId) > 36) {
            $error[] = "*Order Id must be between 3 and 36 characters in length.";
        }elseif (!preg_match('/^(?=.*[0-9])[a-zA-Z0-9\-_\/\\\\]+$/', $orderId)) {
            $error[] = "*Order Id must be numeric, alphanumeric, and can include '-', '_', '/', or '\\'.";
        }


        // Validate Payment Method (must be either "Prepaid" or "COD")
        $paymentMode = strtolower($rowData["*Payment Method(COD/Prepaid)"]);
        if ($paymentMode !== "prepaid" && $paymentMode !== "cod") {
            $error[] = 'Payment mode must be either "Prepaid" or "COD".';
        }

        // Address validation
        if (
            strlen($rowData["*Shipping Full Address"]) < 10
        ) {
            $error[] = "Address should have at least 10 characters";
        }
        

        // Validate Zipcode
        if (
            !preg_match(
                '/^\d{6}$/',
                $rowData["*Shipping Address Zipcode(Pincode)"]
            )
        ) {
            $error[] =
                "*Shipping Address Zipcode (Pincode) must be exactly 6 digits";
        }

        // Validate City and State (alphabetic, spaces, and hyphens allowed)
        if (
            !empty($rowData["*Shipping Address City"]) && 
            strlen($rowData["*Shipping Address City"]) < 3 && 
            strlen($rowData["*Shipping Address City"]) > 50
        ) {
            $error[] =
                "*Shipping Address City must be between 2 and 50 characters";
        }
        if (
            !empty($rowData["*Shipping Address State"]) && 
            strlen($rowData["*Shipping Address State"]) < 2 
        ) {
            $error[] =
                "*Shipping Address State is invalid. It should be have at least 2 characters.";
        }
        //date validation
        $orderDate = $rowData["Order Date [DD-MM-YYYY] (Optional)"];

        // Validate only if the date is not empty
        if (!empty($orderDate) && !preg_match("/^\[\d{2}-\d{2}-\d{4}\]$/", $orderDate)) {
            $error[] = "Order Date [DD-MM-YYYY] (Optional) should be in the format [DD-MM-YYYY].";
        }        
        if (!empty($rowData["Order Notes"]) && mb_strlen($rowData["Order Notes"]) > 500) {
            $error[] = "Order Notes (Optional) should be less than equal to 500 characters.";
        }
        if (
            !filter_var($rowData["*Product Quantity"], FILTER_VALIDATE_INT) ||
            $rowData["*Product Quantity"] < 0
        ) {
            $error[] = "*Product Quantity must be a non-negative integer";
        }

        if (
            empty($rowData["*Selling Price(Per Unit Item, Inclusive of Tax)"])
        ) {
            $error[] =
                "*Selling Price(Per Unit Item, Inclusive of Tax) is required";
        } else {
            // Check if it's a valid non-negative number
            $sellingPrice =
                $rowData["*Selling Price(Per Unit Item, Inclusive of Tax)"];

            // Validate that the price is a valid numeric value and non-negative
            if (!is_numeric($sellingPrice) || $sellingPrice < 0) {
                $error[] =
                    "*Selling Price (Per Unit Item, Inclusive of Tax) must be a valid non-negative number";
            }

            // Ensure the price has at most two decimal places
            if (!preg_match('/^\d+(\.\d{1,2})?$/', $sellingPrice)) {
                $error[] =
                    "*Selling Price (Per Unit Item, Inclusive of Tax) must be a valid non-negative number, with up to two decimal places";
            }
        }
        foreach (
            [
                "*Length (cm)",
                "*Breadth (cm)",
                "*Height (cm)",
                "*Weight Of Shipment(kg)",
            ]
            as $field
        ) {
            if (
                !is_numeric($rowData[$field]) ||
                $rowData[$field] < 0 ||
                !preg_match('/^\d+(\.\d{1,2})?$/', $rowData[$field])
            ) {
                $error[] = "$field must be a valid non-negative number, with up to two decimal places";
            }
        }

        // If there are any validation errors, log them and return the errors
        if (!empty($error)) {
            $r = implode("\n", $error);
            return ["valid" => false, "error" => $r];
        }

        return ["valid" => true];
    }

    public function import(Request $request, OrderService $orderService)
    {
        $companyId = session("company_id");
        // Validate uploaded file
        $validator = Validator::make($request->all(), [
            "importfile" => "required|mimes:csv,txt|max:2048",
        ]);

        if ($validator->fails()) {
            $importfileerror = $validator->errors();
            if ($importfileerror->has("importfile")) {
                return redirect()
                    ->route("add_orders")
                    ->with(["error" => $importfileerror->first("importfile")]);
            }
        }

        $file = $request->file("importfile");
        $filePath = $file->getRealPath();

        // Open the CSV file
        $data = array_map("str_getcsv", file($filePath));
        $header = array_map("trim", $data[0]);
        //return $header;
        unset($data[0]); // Remove header row
        $expectedFields = [
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
        ];
        $errors = [];
        // Validate header
        foreach ($expectedFields as $field) {
            if (!in_array($field, $header)) {
                $errors[] = "Missing required column: {$field}";
            }
        }
        if ($errors) {
            return redirect()
                ->route("add_orders")
                ->with(["error" => implode(";", $errors)]);
        }

        $errorRows = [];
        $rows = [];
        foreach ($data as $index => $row) {
            //$rowData = array_combine($header, array_map("trim", $row));
            $trimmedRow = array_map("trim", $row);
            // Skip empty or malformed rows
            if (!is_array($trimmedRow) || count($header) !== count($trimmedRow)) {
                \Log::error('Skipping invalid row during import', [
                    'header_count' => count($header),
                    'row_count' => count($trimmedRow),
                    'row_raw' => $row,
                    'row_trimmed' => $trimmedRow,
                    'line_index' => $index,
                ]);

                $errorRows[] = array_merge(
                    ['Error' => 'Header and data column count mismatch'],
                    array_combine(array_pad($header, count($trimmedRow), ''), $trimmedRow + array_fill(0, count($header), ''))
                );

                continue;
            }

            $rowData = array_combine($header, $trimmedRow);

            $orderDate = $rowData["Order Date [DD-MM-YYYY] (Optional)"];
        
            $rowValidation = $this->validateRow($rowData);
        
            if (!$rowValidation["valid"]) {
                $rowData["Order Date [DD-MM-YYYY] (Optional)"] = $orderDate;
        
                $errorRows[] = array_merge(
                    ["Error" => $rowValidation["error"]],
                    $rowData
                );
                continue;
            } else {
                $cleanOrderDate =str_replace(["[", "]"], "", $orderDate);
            }
            // $rowData['Order Date [DD-MM-YYYY] (Optional)'] = date('Y-m-d',strtotime($rowData['Order Date [DD-MM-YYYY] (Optional)']));
            $billing_country = $shipping_country = Country::where(
                "country_name",
                $rowData["*Shipping Address Country"]
            )
                ->orWhere("country_code", $rowData["*Shipping Address Country"])
                ->first();
            if (
                $rowData["*Shipping Address Country"] !=
                $rowData["Billing Address Country"]
            ) {
                $billing_country = Country::where(
                    "country_name",
                    $rowData["Billing Address Country"]
                )
                    ->orWhere(
                        "country_code",
                        $rowData["Billing Address Country"]
                    )
                    ->first();
            }

            $billing_state = $shipping_state = State::where(
                "name",
                $rowData["*Shipping Address State"]
            )
                ->orWhere("state_code", $rowData["*Shipping Address State"])
                ->first();
            if (!empty($rowData["Billing Address State"]) && 
                $rowData["*Shipping Address State"] !=
                $rowData["Billing Address State"]
            ) {
                $billing_state = State::where(
                    "name",
                    $rowData["Billing Address State"]
                )
                    ->orWhere("state_code", $rowData["Billing Address State"])
                    ->first();
                if (empty($billing_state)) {
                    $errorRows[] = array_merge(
                        ["Error" => "Billing Address State is not found"],
                        $rowData
                    );
                    continue;
                }
            }
            if (empty($shipping_state)) {
                $errorRows[] = array_merge(
                    ["Error" => "Shipping Address State is not found"],
                    $rowData
                );
                continue;
            }
            $order_products[$rowData["*Order Id"]][] = [
                "product_name" => $rowData["*Product Name"],
                "sku" => $rowData["*Product SKU"],
                "line_item_id"=>null,
                "unit_price" =>
                    $rowData[
                        "*Selling Price(Per Unit Item, Inclusive of Tax)"
                    ] ?? 0,
                "quantity" => $rowData["*Product Quantity"] ?? 1,
                "discount" => !empty($rowData["Discount(Per Unit Item)"])
                    ? $rowData["Discount(Per Unit Item)"]
                    : 0,
                "shipping" => !empty($rowData["Shipping Charges(Per Order)"])
                    ? $rowData["Shipping Charges(Per Order)"]
                    : 0,
                "hsn" => $rowData["HSN Code"] ?? "",
                "tax_rate" => !empty($rowData["*Tax %"])
                    ? $rowData["*Tax %"]
                    : 0,
                "tax_name" => null,
                "tax_type" => 0,
                "tax_amount" => 0,
                "total_price" =>
                    $rowData[
                        "*Selling Price(Per Unit Item, Inclusive of Tax)"
                    ] * $rowData["*Product Quantity"],
            ];
            $price =
                (float) $rowData[
                    "*Selling Price(Per Unit Item, Inclusive of Tax)"
                ];
            $quantity = (int) $rowData["*Product Quantity"];
            $subtotal = $price * $quantity;
            $discount = (float) $rowData["Total Discount (Per Order)"];
            $shipping = (float) $rowData["Shipping Charges(Per Order)"];
            $tax = (float) $rowData["Tax %"];
            $codCharges = (float) $rowData["COD Charges(Per Order)"];
            $giftwrap = (float) $rowData["Gift Wrap Charges(Per Order)"];
            $order_totals = [];
            if ($discount > 0) {
                $order_totals[] = [
                    "title" => "Discount",
                    "code" => "discount",
                    "value" => $discount,
                    "sort_order" => 4,
                ];
            }
            if ($shipping > 0) {
                $order_totals[] = [
                    "title" => "Shipping",
                    "code" => "shipping",
                    "value" => $shipping,
                    "sort_order" => 2,
                ];
            }
            // if($tax>0){
            //     $order_totals[]= [
            //         "title" => "Tax",
            //         "code" => "tax",
            //         "value" => $tax,
            //         "sort_order" => 3
            //     ];
            // }
            if ($giftwrap > 0) {
                $order_totals[] = [
                    "title" => "Giftwrap",
                    "code" => "giftwrap",
                    "value" => $giftwrap,
                    "sort_order" => 5,
                ];
            }
            if ($codCharges > 0) {
                $order_totals[] = [
                    "title" => "COD Charges",
                    "code" => "cod_charges",
                    "value" => $codCharges,
                    "sort_order" => 6,
                ];
            }
            $channel_order_date =  (new \DateTime($cleanOrderDate))->format("Y-m-d H:i:s");
            $rows[$rowData["*Order Id"]] = [
                "vendor_order_id" => $rowData["*Order Id"],
                "vendor_order_number" => $rowData["*Order Id"],
                "channel_id" => $rowData["*Sales Channel Id"],
                "channel_order_date" => $channel_order_date,
                "fullname" => $rowData["*Buyer Full Name"],
                "email_id" => $rowData["Buyer Email (Optional)"] ?? null,
                "email" => $rowData["Buyer Email (Optional)"] ?? null,
                "phone_number" => $rowData["*Buyer Mobile"],
                "s_fullname" => $rowData["*Buyer Full Name"],
                "s_company" => null,
                "s_phone" => $rowData["*Buyer Mobile"],
                "s_complete_address" => $rowData["*Shipping Full Address"],
                "s_landmark" => null,
                "s_zipcode" => !empty(
                    $rowData["*Shipping Address Zipcode(Pincode)"]
                )
                    ? (int) $rowData["*Shipping Address Zipcode(Pincode)"]
                    : null,
                "s_city" => $rowData["*Shipping Address City"],
                "s_state_code" => $shipping_state->state_code ?? null,
                "s_country_code" => $shipping_country->country_code ?? null,
                "b_fullname" => $rowData["*Buyer Full Name"],
                "b_company" => null,
                "b_complete_address" => $rowData["Billing Full Address"],
                "b_landmark" => null,
                "b_zipcode" => !empty($rowData["Billing Address Postcode"])
                    ? (int) $rowData["Billing Address Postcode"]
                    : null,
                "b_phone" => $rowData["*Buyer Mobile"],
                "b_city" => !empty($rowData["Billing Address City"])
                    ? $rowData["Billing Address City"]
                    : null,
                "b_state_code" => $billing_state->state_code ?? null,
                "b_country_code" => $billing_country->country_code ?? null,
                "payment_mode" => strtolower($rowData["*Payment Method(COD/Prepaid)"]),
                "payment_method" => $rowData["*Payment Method(COD/Prepaid)"],
                "currency_code" => "INR",
                "financial_status" => null,
                "package_length" => $rowData["*Length (cm)"],
                "package_breadth" => $rowData["*Breadth (cm)"],
                "package_height" => $rowData["*Height (cm)"],
                "package_dead_weight" => $rowData["*Weight Of Shipment(kg)"],
                "order_totals" => $order_totals,
                "order_products" => $order_products[$rowData["*Order Id"]],
                "order_tags" => $rowData["Order tags"],
                "notes" => $rowData["Order Notes"],
            ];
        }
        $alreadyImported = 0;
        $updatedOrders =0;
        $total_orders = 0;
        $result = [];
        try {
            foreach ($rows as $order) {
                $existorder = $orderService->isOrderAlreadyImported($order["vendor_order_number"],$companyId,$order["channel_id"]);
                if ($existorder) {
                    if($existorder->status_code=='N'){
                        $orderService->updateOrderAddress($existorder,$order);
                        $updatedOrders++;
                        continue;
                    }
                    $alreadyImported++;
                    continue;
                }
                try {
                    $subtotal = 0;
                    foreach ($order["order_products"] as $order_product) {
                        $subtotal += $order_product["total_price"];
                    }
                    $order["sub_total"] = $subtotal;
                    $total = $subtotal;
                    foreach ($order["order_totals"] as $order_totals) {
                        if ($order_totals["code"] == "discount") {
                            $total -= $order_totals["value"];
                        } else {
                            $total += $order_totals["value"];
                        }
                    }
                    $order["order_total"] = $total;
                    $order["order_totals"][] = [
                        "title" => "Subtotal",
                        "code" => "sub_total",
                        "value" => $subtotal,
                        "sort_order" => 1,
                    ];
                    $order["order_totals"][] = [
                        "title" => "Total",
                        "code" => "total",
                        "value" => $total,
                        "sort_order" => 9,
                    ];

                    $buyer_invoice_settings = BuyerInvoiceSetting::where(
                        "company_id",
                        $companyId
                    )->first();
                    $invoice_type =
                        $buyer_invoice_settings->number_type ?? "order_number";
                    $invoice_prefix = "";
                    $invoice_start_from = "";
                    if ($invoice_type == "custom_number") {
                        $invoice_prefix = $buyer_invoice_settings->prefix ?? "";
                        $invoice_start_from =
                            $buyer_invoice_settings->start_from ?? "";
                    }
                    $order["invoice_type"] = $invoice_type;
                    $order["invoice_prefix"] = $invoice_prefix;
                    $order["invoice_start_from"] = $invoice_start_from;
                    $orderService->createOrder($order, $companyId);
                    $total_orders++;
                } catch (\Exception $e) {
                    $result["error"][] = $e->getMessage();
                }
            }
            if (count($errorRows) > 0) {
                $errorFile = $this->createErrorCsv($header,$errorRows);
                $errorFile = route("download_error_csv", [
                    "filename" => $errorFile,
                ]);
                $result["error"][] =
                    'Some orders were not imported. <a href="' .
                    $errorFile .
                    '" target="_blank">Click here to download the file with error remarks.</a>';
            }

            if ($total_orders > 0) {
                $result["success"][] =
                    $total_orders . " Orders have been imported successfully.";
            }
            if ($updatedOrders > 0) {
                $result["success"][] =
                    $updatedOrders . " Orders have been udpated successfully.";
            }
            if ($alreadyImported > 0) {
                $result["error"][] =
                    $alreadyImported . " Orders have already been imported.";
            }
            if (isset($result["error"])) {
                $result["error"] = implode("\n", $result["error"]);
            }
            if (isset($result["success"])) {
                $result["success"] = implode("\n", $result["success"]);
            }
            return redirect()
                ->route("add_orders")
                ->with($result);
        } catch (\Exception $e) {
            return redirect()
                ->route("add_orders")
                ->with("error", "Error importing data: " . $e->getMessage());
        }
    }

    public function createErrorCsv($header,$errorRows)
    {
        $filename = "Error_bulk_orders_import_template_" . time() . ".csv";
        $path = storage_path("app/public/" . $filename);
        $handle = fopen($path, "w");
        array_unshift($header, 'Error');
        fputcsv($handle, $header);
        foreach ($errorRows as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);

        return $filename;
    }
    public function downloadErrorCsv($filename)
    {
        $filePath = storage_path("app/public/" . $filename);
        if (file_exists($filePath)) {
            return response()->download($filePath);
        } else {
            return redirect()
                ->route("add_orders")
                ->with("error", "Error file not found.");
        }
    }

    public function exportOrders(Request $request)
    {
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            DB::reconnect();
        }

        DB::connection()->disableQueryLog();

        $selectedOrderIds = $request->input("selectedOrderIds", []);
        $tab = $request->input("tab", '');
        $filters = $request->all();      
        $companyId = session('company_id');
        $queryFilters=[];
        if($queryFilters){
            $queryFilters = $this->getcsvTabFilters($tab);
        }
        if (empty($tab) && empty($selectedOrderIds)) {
            return response()->json(["error" => "No orders selected."], 400);
        }

        // Fixing the alias issue
        $query = Order::where('company_id', $companyId);

        // Inject selectedOrderIds into filters so the export logic picks them
        $filters['selectedOrderIds'] = $selectedOrderIds;

        $fileName = "Orders_csv" . "_" . time() . ".csv";

        return Excel::download(new OrderExport($query, $queryFilters, $filters), $fileName);
    }

    public function createOrder(Request $request)
    {
        // Create the order using a factory
        $order = Order::factory()->create();
        session()->flash("success", "The test order has been created successfully.");
        // Return a JSON response that contains the success message and an action flag
        return;
    }

    public function cancelOrders(Request $request)
    {
        $companyId = session('company_id');
        $order_ids = $request->order_ids ?? [];
        if ($order_ids && !is_array($order_ids)) {
            $order_ids = [$order_ids];
        }
        $status = $request->status_code ?? "";
        if (empty($order_ids) || !is_array($order_ids)) {
            session()->flash("error", "Invalid or missing order IDs.");
            return;
        }
        if (empty($status)) {
            session()->flash("error", "status is required");
            return;
        }
        if ($status != "C") {
            session()->flash("error", "status is invalid");
            return;
        }
        Order::whereIn("id", $order_ids)->where('company_id',$companyId)->where('status_code','N')->update(["status_code" => $status]);
        session()->flash("success", "Order has been cancelled successfully.");
        return response()->json([
            "success" => true,
            "message" => session("success"),
        ]);
    }
    public function completedorders(Request $request)
    {
        $order_ids = $request->order_ids ?? [];
        if ($order_ids && !is_array($order_ids)) {
            $order_ids = [$order_ids];
        }
        $status = $request->status_code ?? "";
        if (empty($order_ids) || !is_array($order_ids)) {
            session()->flash("error", "Invalid or missing order IDs.");
            return;
        }
        if (empty($status)) {
            session()->flash("error", "status is required");
            return;
        }
        if ($status != "F") {
            session()->flash("error", "status is invalid");
            return;
        }
        Order::whereIn("id", $order_ids)->update(["status_code" => $status]);
        session()->flash("success", "Order has completed successfully.");
        return response()->json([
            "success" => true,
            "message" => session("success"),
        ]);
    }
    public function archiveOrders(Request $request)
    {
        $companyId = session('company_id');
        $order_ids = $request->order_ids ?? [];
        if ($order_ids && !is_array($order_ids)) {
            $order_ids = [$order_ids];
        }
        $status_code = $request->status_code ?? "";
        if (empty($order_ids) || !is_array($order_ids)) {
            session()->flash("error", "Invalid or missing order IDs.");
            return;
        }
        if (empty($status_code)) {
            session()->flash("error", "status_code is required");
            return;
        }
        if ($status_code != "A") {
            session()->flash("error", "status_code is invalid");
            return;
        }
        Order::whereIn('id', $order_ids)->where('company_id',$companyId)->where('status_code','N')->delete();
        // Order::whereIn("id", $order_ids)->update([
        //     "status_code" => $status_code,
        // ]);
        session()->flash("success", "Order has been archived successfully.");
        return response()->json([
            "success" => true,
            "message" => session("success"), // Send the success message
        ]);
    }
    public function shippedOrders(Request $request)
    {
        $manifest_ids = $request->manifest_ids ?? [];
        if ($manifest_ids && !is_array($manifest_ids)) {
            $manifest_ids = [$manifest_ids];
        }
        $status = $request->status_code ?? "";
        if (empty($manifest_ids) || !is_array($manifest_ids)) {
            session()->flash("error", "Invalid or missing manifest IDs.");
            return;
        }
        if (empty($status)) {
            session()->flash("error", "status is required");
            return;
        }
        if ($status != "S") {
            session()->flash("error", "status is invalid");
            return;
        }
        $order_ids = ManifestOrder::whereIn('manifest_id', $manifest_ids)->pluck('order_id');       
        Order::whereIn("id", $order_ids)->update(["status_code" => $status]);
        session()->flash("success", "Order has been shipped successfully.");
        return response()->json([
            "success" => true,
            "message" => session("success"),
        ]);
    }
    public function onholdOrders(Request $request)
    {
        $companyId = session('company_id');
        $order_ids = $request->order_ids ?? [];
        if ($order_ids && !is_array($order_ids)) {
            $order_ids = [$order_ids];
        }
        $status_code = $request->status_code ?? "";
        if (empty($order_ids) || !is_array($order_ids)) {
            session()->flash("error", "Invalid or missing order IDs.");
            return;
        }
        if (empty($status_code)) {
            session()->flash("error", "status_code is required");
            return;
        }
        if ($status_code != "H") {
            session()->flash("error", "status_code is invalid");
            return;
        }
        Order::whereIn("id", $order_ids)->where('company_id',$companyId)->where('status_code','N')->update([
            "status_code" => $status_code,
        ]);
        session()->flash("success", "Order has been put on hold successfully.");
        return response()->json([
            "success" => true,
            "message" => session("success"), // Send the success message
        ]);
    }
    public function unfulfilled_orders()
    {
        $companyId = session("company_id");
        $orders = Order::with(["orderLogs", "shipmentInfo", "channelSetting"])
            ->whereHas("shipmentInfo", function ($query) {
                $query->where("fulfillment_status", 3);
            })
            ->where("company_id", $companyId)
            ->get();
        
        return view("seller.orders.unfulfilled", ["orders" => $orders]);
    }
    public function buyer_invoice()
    {
        $companyId = session("company_id");
        if (!$companyId) {
            return redirect()
                ->route("dashboard")
                ->with("error", "Company ID not found in session.");
        }

        $buyerInvoiceSettings = BuyerInvoiceSetting::where(
            "company_id",
            $companyId
        )->first();

        return view("seller.orders.buyer_invoice", compact("buyerInvoiceSettings"));
    }
    public function store_buyer_invoice(Request $request)
    {
        $companyId = session("company_id");
        if (!$companyId) {
            return redirect()
                ->route("order_invoice")
                ->with("error", "Company ID not found in session.");
        }

        try {
            // Ensure prefix and start_from are set or null if empty
            $data = $request->only(
                "number_type",
                "prefix",
                "start_from",
                "invoice_type"
            );
            $data["prefix"] = $data["prefix"] ?? null;
            $data["start_from"] = $data["start_from"] ?? null;

            // Create or update the buyer invoice setting
            BuyerInvoiceSetting::updateOrCreate(
                ["company_id" => $companyId],
                $data + ["company_id" => $companyId]
            );

            return redirect()
                ->route("order_invoice")
                ->with(
                    "success",
                    "Invoice settings have been updated successfully."
                );
        } catch (\Exception $e) {
            return redirect()
                ->route("order_invoice")
                ->with("error", "An error occurred: " . $e->getMessage());
        }
    }

    public function buyer_shipping_label()
    {
        $companyId = session("company_id");
        if (!$companyId) {
            return redirect()
                ->route("label")
                ->with("error", "Company ID not found in session.");
        }
        $buyerShippingSettings = BuyerShippingLabelSetting::where("company_id",$companyId)->first();

        $settings = json_decode(optional($buyerShippingSettings)->extra_details, true);
        $isupdate = $buyerShippingSettings ? true : false;

        return view('seller.orders.shipping_label', compact('buyerShippingSettings', 'settings', 'isupdate'));
    }

    public function store_buyer_shipping_label(Request $request)
    {
        $companyId = session("company_id");
        if (!$companyId) {
            return redirect()->back()->with("error", "Company ID not found in session.");
        }

        try {
            $extraDetails = [
                'hide_order_amount'   => $request->has('hide_order_amount'),
                'hide_buyer_mobile'   => $request->has('hide_buyer_mobile'),
                'hide_shipper_mobile' => $request->has('hide_shipper_mobile'),
                'hide_return_address' => $request->has('hide_return_address'),
                'hide_product_name'   => $request->has('hide_product_name'),
                'hide_product_sku'    => $request->has('hide_product_sku'),
                'enable_logo'           => $request->has('enable_logo'),
                'enable_order_id_barcode'    => $request->has('enable_order_id_barcode'),
                'order_notes'    => $request->has('order_notes'),
                'auto_shipped'    => $request->has('auto_shipped'),
                'notes'    => $request->post('notes'),
            ];
            $settings = BuyerShippingLabelSetting::updateOrCreate(
                ['company_id' => $companyId],
                [
                    'company_id'    => $companyId,
                    'extra_details' => json_encode($extraDetails),
                ]
            );
            $message = $settings->wasRecentlyCreated ? 'Settings have been saved successfully.' : 'Settings have been updated successfully.';
            return redirect()->route("label")->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function createOrderApi(Request $request, OrderService $orderService)
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        try {
            $validated = $request->validate([
                "vendor_order_id" => "required|string",
                "vendor_order_number" => "required|string",
                "channel_id" => "required|exists:channels,id",
                "channel_order_date" => "required|date",
                "fullname" => "required|string",
                "email" => "required|email",
                "phone_number" => "required|string",
                // Shipping details
                "s_fullname" => "required|string|min:3",
                "s_company" => "nullable|string",
                "s_complete_address" => "required|string|min:10",
                "s_landmark" => "nullable|string",
                "s_phone" => "required|string",
                "s_zipcode" => "required|integer",
                "s_city" => "required|string",
                "s_state_code" => "required|string|min:2|max:2",
                "s_country_code" => "required|string|min:2|max:2",
                // Billing details
                "b_fullname" => "required|string|min:3",
                "b_company" => "nullable|string",
                "b_complete_address" => "required|string|min:10",
                "b_landmark" => "nullable|string",
                "b_phone" => "required|string",
                "b_zipcode" => "required|integer",
                "b_city" => "required|string",
                "b_state_code" => "required|string|min:2|max:2",
                "b_country_code" => "required|string|min:2|max:2",
                "notes" => "nullable|string",
                "package_breadth" => "nullable|numeric",
                "package_height" => "nullable|numeric",
                "package_length" => "nullable|numeric",
                "package_dead_weight" => "nullable|numeric",
                "currency_code" => "required|string",
                "financial_status" => "nullable|string",
                "payment_mode" => "required|string",
                "payment_method" => "required|string",
                "sub_total" => "required|numeric",
                "order_total" => "required|numeric",
                // order_products
                "order_products" => "required|array",
                "order_products.*.product_name" => "required|string",
                "order_products.*.sku" => "required|string",
                "order_products.*.unit_price" => "required|numeric",
                "order_products.*.quantity" => "required|integer",
                "order_products.*.discount" => "nullable|numeric",
                "order_products.*.shipping" => "nullable|numeric",
                "order_products.*.hsn" => "nullable|string",
                "order_products.*.tax_rate" => "nullable|numeric",
                "order_products.*.tax_amount" => "nullable|numeric",
                "order_products.*.total_price" => "required|numeric",
                "order_totals" => "required|array",
                "order_totals.*.title" => "required|string|max:255",
                "order_totals.*.code" => "required|string|max:100",
                "order_totals.*.value" => "required|numeric|min:0",
                "order_totals.*.sort_order" => "required|integer|min:0",
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if (request()->expectsJson() || request()->is("api/*")) {
                return response()->json(
                    [
                        "success" => false,
                        "errors" => $e->validator->errors(),
                    ],
                    422
                );
            }
        }

        try {
            
            $existorder = $orderService->isOrderAlreadyImported(
                $request["vendor_order_number"],
                $companyId,
                $request["channel_id"]
            );

            if ($existorder) {
                if ($existorder->status_code === 'N') {

                    $orderService->updateOrderAddress($existorder, $request->all());

                    if ($request->wantsJson() || $request->is("api/*")) {
                        return response()->json([
                            "message" => "Existing order address has been updated successfully!",
                            "order" => $existorder
                        ]);
                    } else {
                        return redirect()
                            ->route("order_list")
                            ->with("success", "Existing order address has been updated successfully!");
                    }
                } else {
                
                    if ($request->wantsJson() || $request->is("api/*")) {
                        return response()->json([
                            "error" => "Order already exists and cannot be updated!"
                        ], 409); // 409 Conflict
                    } else {
                        return redirect()
                            ->back()
                            ->withErrors(["error" => "Order already exists and cannot be updated!"]);
                    }
                }
            }

            // Invoice settings
            $buyer_invoice_settings = BuyerInvoiceSetting::where(
                "company_id",
                $companyId
            )->first();

            $invoice_type = $buyer_invoice_settings->number_type ?? "order_number";
            $invoice_prefix = "";
            $invoice_start_from = "";

            if ($invoice_type == "custom_number") {
                $invoice_prefix = $buyer_invoice_settings->prefix ?? "";
                $invoice_start_from = $buyer_invoice_settings->start_from ?? "";
            }

            $request["invoice_type"] = $invoice_type;
            $request["invoice_prefix"] = $invoice_prefix;
            $request["invoice_start_from"] = $invoice_start_from;

            
            $order = $orderService->createOrder($request, $companyId);

            if ($request->wantsJson() || $request->is("api/*")) {
                return response()->json(
                    [
                        "message" => "Order has been created successfully!",
                        "order" => $order,
                    ],
                    201
                );
            } else {
                return redirect()
                    ->route("order_list")
                    ->with("success", "Order has been created successfully!");
            }
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->is("api/*")) {
                return response()->json(
                    ["error" => "Order creation failed!"],
                    500
                );
            } else {
                return redirect()
                    ->back()
                    ->withErrors(["error" => "Order creation failed!"]);
            }
        }
    }
    /* */
    public function apiGetOrders(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'User is not authenticated'], 401);
            }

            $companyId = $request->company_id ?? $user->company_id;

            // Get all orders without pagination
            $orders = Order::where('company_id', $companyId)
                ->orderBy('channel_order_date', 'desc')
                ->get(); 

            return response()->json([
                'success' => true,
                'orders' => $orders
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching orders: ' . $e->getMessage());
            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function updatePackage(Request $request)
    {
        $order = Order::find($request->order_id);
        if (!$order) {
            Log::warning('Order not found for ID: ' . $request->order_id);
            return response()->json(['error' => 'Order not found'], 404);
        }

        $order->package_dead_weight = $request->dead_weight;
        $order->package_length = $request->length;
        $order->package_breadth = $request->breadth;
        $order->package_height = $request->height;
        $order->save();
        session()->flash("success", "Package details have been updated successfully.");
            return response()->json([
                "success" => true,
                "message" => session("success"),
        ]);
    }
    public function cloneOrder(Request $request)
    {
        $orderId = $request->order_id??0; 
        $companyId = session('company_id')??0;
        
        if($orderId && $companyId){
            //Get custom channel
            $settings = ChannelSetting::where('company_id', $companyId)->where('status', 1)->where('channel_code', 'custom')->first();
            if ($settings) {
                DB::beginTransaction();
                try {
                    $original = Order::with(['orderProducts', 'orderTotals'])->findOrFail($orderId);
                    // Clone Order (except for primary key and timestamp fields)   
                    $key = "cloned_order_$orderId";

                    if (Cache::has($key)) {
                        session()->flash("error", 'Parent order number can be cloned only once');
                        return response()->json(['error' => false, 'error' => 'Parent order number can be cloned only once.']);
                    }         
                    
                    $newOrder = $original->replicate();
                    $newOrder->vendor_order_id = $newOrder->vendor_order_id.'C';
                    $newOrder->vendor_order_number = $newOrder->vendor_order_number.'C';
                    $newOrder->channel_id = $settings->channel_id;
                    $newOrder->status_code = 'N';
                    $newOrder->created_at = now();
                    $newOrder->updated_at = now();
                    $newOrder->save();

                    // Clone related OrderProducts
                    foreach ($original->orderProducts as $product) {
                        $newProduct = $product->replicate();
                        $newProduct->order_id = $newOrder->id;
                        $newProduct->fulfillment_id = null;
                        $newProduct->save();
                    }

                    // Clone related OrderTotals
                    foreach ($original->orderTotals as $total) {
                        $newTotal = $total->replicate();
                        $newTotal->order_id = $newOrder->id;
                        $newTotal->save();
                    }
                    Cache::put($key, true, now()->addDays(7));     
                    DB::commit();
                    $order_view = route('order_view',$newOrder->id);
                    session()->flash("success", 'Order has been cloned successfully. New Order ID:  <a href="' . $order_view . '">'. $newOrder->vendor_order_number.'</a>');
                    return response()->json(['message' => 'Order cloned successfully', 'new_order_id' => $newOrder->vendor_order_number]);

                } catch (\Exception $e) {
                    DB::rollBack();
                    session()->flash("error", 'Failed to clone the order. Details '.$e->getMessage());
                    return response()->json(['error' => 'Failed to clone order', 'Details' => $e->getMessage()], 500);
                }

            }else{
                $url = route('channels_list');
                session()->flash("error", 'Custom store does not exist. Please create a custom store first. <a target="_blank" href="' . $url . '">Click here</a>');
                return response()->json(['message' => 'Custom store does not exist. Please create a custom store first. <a target="_blank" href="' . $url . '">Click here</a>']);
            }
            
        }else{
            session()->flash("error", "order id is required");
            return response()->json(['message' => 'order id is required']);
        }    
    }
    
   public function updateOrderProducts(Request $request)
{
    // Validate request
    try {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'order_products' => 'required|array',
            'order_products.*.product_name' => 'required|string',
            'order_products.*.product_sku' => 'required|string',
            'order_products.*.qty' => 'required|integer|min:1',
        ], [
            'order_products.*.qty.required' => 'Quantity is required.',
            'order_products.*.qty.integer' => 'Quantity must be a number.',
            'order_products.*.qty.min' => 'Quantity must be at least 1.',
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        $firstError = collect($e->validator->errors()->all())->first();
        return redirect()->back()->with(['error' => $firstError]);
    }

    $orderId = $request->order_id;
    $updates = $request->input('order_products');

    $order = Order::with(['orderProducts', 'orderTotals'])->findOrFail($orderId);

    // Validate product IDs
    $validIds = $order->orderProducts->pluck('id')->all();
    foreach (array_keys($updates) as $id) {
        if (!in_array($id, $validIds, true)) {
            return redirect()->back()->with(["error" => "Order-product id {$id} does not belong to order {$orderId}"]);
        }
    }

    DB::beginTransaction();
    try {
        foreach ($updates as $orderProductId => $data) {
            $orderProduct = $order->orderProducts->firstWhere('id', $orderProductId);

            $qty = $data['qty'];

            // Calculate per-unit values
            $perUnitDiscount = $orderProduct->discount > 0 ? $orderProduct->discount / $orderProduct->quantity : 0;
            $perUnitTax = $orderProduct->tax_amount > 0 ? $orderProduct->tax_amount / $orderProduct->quantity : 0;
            $perUnitShipping = $orderProduct->shipping > 0 ? $orderProduct->shipping / $orderProduct->quantity : 0;

            // Update product
            $orderProduct->product_name = $data['product_name'];
            $orderProduct->sku = $data['product_sku'];
            $orderProduct->quantity = $qty;
            $orderProduct->discount = round($perUnitDiscount * $qty, 2);
            $orderProduct->tax_amount = round($perUnitTax * $qty, 2);
            $orderProduct->shipping = round($perUnitShipping * $qty, 2);
            $orderProduct->total_price = round($orderProduct->unit_price * $qty, 2);

            $orderProduct->save();
        }

        $this->recalculateOrderTotal($orderId);

        DB::commit();
        return redirect()->back()->with(['success' => 'Order products have been updated successfully.']);
    } catch (\Throwable $e) {
        DB::rollBack();
        return redirect()->back()->with(['error' => 'Error updating order products: ' . $e->getMessage()]);
    }
}

protected function recalculateOrderTotal(int $orderId): void
{
    $order = Order::with(['orderProducts', 'orderTotals'])->findOrFail($orderId);

    $subTotal = $order->orderProducts->sum('total_price');
    $totalTax = $order->orderProducts->sum('tax_amount');
    $totalDiscount = $order->orderProducts->sum('discount');
    $totalShipping = $order->orderProducts->sum('shipping');

    $orderTotals = $order->orderTotals->keyBy('code');

    // Handle old totals if needed
    $oldSubTotal = $orderTotals['sub_total']->value ?? 0;
    $oldDiscount = $orderTotals['discount']->value ?? 0;
    $oldGiftwrap = $orderTotals['giftwrap']->value ?? 0;
    $oldCodCharges = $orderTotals['cod_charges']->value ?? 0;
    $oldShipping = $orderTotals['shipping']->value ?? 0;
    $oldAdvance = $orderTotals['advanced']->value ?? 0;

    $newTotal = $subTotal - $totalDiscount + $totalShipping;

    // Adjust discount proportionally if previously applied
    if ($totalDiscount == 0 && $oldDiscount > 0 && $oldSubTotal > 0) {
        $totalDiscount = round(($subTotal * $oldDiscount) / $oldSubTotal, 2);
        $newTotal -= $totalDiscount;
    }

    // Adjust COD and giftwrap proportionally
    if ($oldCodCharges > 0 && $oldSubTotal > 0) {
        $totalCodCharges = round(($subTotal * $oldCodCharges) / $oldSubTotal, 2);
        $newTotal += $totalCodCharges;
    }

    if ($oldGiftwrap > 0 && $oldSubTotal > 0) {
        $totalGiftwrap = round(($subTotal * $oldGiftwrap) / $oldSubTotal, 2);
        $newTotal += $totalGiftwrap;
    }
    if ($oldShipping > 0 && $oldSubTotal > 0) {
        $totalShipping = round(($subTotal * $oldShipping) / $oldSubTotal, 2);
        $newTotal += $totalShipping;
    }
    if ($oldAdvance > 0 && $oldSubTotal > 0) {
        $totalAdvancedPaid = round(($subTotal * $oldAdvance) / $oldSubTotal, 2);
        $newTotal -= $totalAdvancedPaid;
    }

    // Save order totals
    $order->sub_total = $subTotal;
    $order->order_total = $newTotal;
    $order->save();

    // Update order_totals table
    $updates = [
        'sub_total' => $subTotal,
        'total' => $newTotal,
        'tax' => $totalTax,
        'discount' => $totalDiscount,
        'cod_charges' => $totalCodCharges ?? 0,
        'giftwrap' => $totalGiftwrap ?? 0,
        'shipping' => $totalShipping,
        'advanced' => $totalAdvancedPaid??0,
    ];

    foreach ($updates as $code => $value) {
        OrderTotal::where('order_id', $orderId)
                ->where('code', $code)
                ->update(['value' => $value]);
    }
}

    public function deleteOrderProduct(Request $request)
    {
        $orderProductId = $request->order_product_id;

        DB::beginTransaction();

        try {
            $orderProduct = OrderProduct::findOrFail($orderProductId);
            $order = Order::with('orderProducts')->findOrFail($orderProduct->order_id);

            if (count($order->orderProducts) <= 1) {
                session()->flash("error", 'Cannot delete the only product in the order.');
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete the only product in the order.',
                ]);
            }else{
                $orderProduct->delete();

                $this->recalculateOrderTotal($order->id);

                DB::commit();
                session()->flash("success", 'Order product has been deleted successfully.');
                return response()->json([
                    'success' => true,
                    'message' => 'Product has been deleted successfully.',
                    'order_total' => $order->order_total,
                ]);
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash("success",  $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }
    public function markPaidOrder(Request $request)
    {
        $companyId = session('company_id');
        $order_id = $request->order_id ?? '';
        $channel_code = $request->channel_code ?? '';

        if (empty($order_id)) {
            session()->flash("error", "Invalid or missing order ID.");
            return back();
        }

        if (empty($channel_code)) {
            session()->flash("error", "channel_code is required");
            return back();
        }

        $order = Order::where("id", $order_id)
            ->where("company_id", $companyId)
            ->where('status_code', 'N')
            ->first();

        if (!empty($order)) {
            $vendor_order_id = $order->vendor_order_id;

            if ($channel_code == 'shopify') {
                $setting = ChannelSetting::where('company_id', $companyId)
                    ->where('channel_code', 'shopify')
                    ->where('channel_id', $order->channel_id)
                    ->where('status', 1)
                    ->first();

                if (!$setting) {
                    session()->flash("error", "Shopify channel settings not found.");
                    return back();
                }

                $shopifyGraphQLService = new ShopifyGraphQLService();
                $response = $shopifyGraphQLService->markPaid(
                    $setting->channel_url,
                    $setting->secret_key,
                    $vendor_order_id
                );

                if (isset($response['success']) && $response['success']) {
                    $order->update([
                        "financial_status" => "paid",
                        "payment_mode"     => "prepaid",
                        "payment_method"   => "Prepaid"
                    ]);

                    OrderLog::create([
                        'company_id'      => $companyId,
                        'order_id'        => $order_id,
                        'vendor_order_id' => $order->vendor_order_id,
                        'type'            => "mark_paid",
                        'response'        => json_encode(['message' => "Order marked as paid via Shopify"]),
                        'status'          => 1,
                    ]);

                    session()->flash("success", "Order has been marked as paid successfully.");
                } else {
                    session()->flash("error", $response['error']??"Failed to mark the order as paid in Shopify.");
                }
            } else {
                // Non-Shopify order
                $order->update([
                    "financial_status" => "paid",
                    "payment_mode"     => "prepaid",
                    "payment_method"   => "Prepaid"
                ]);

                OrderLog::create([
                    'company_id'      => $companyId,
                    'order_id'        => $order_id,
                    'vendor_order_id' => $order->vendor_order_id,
                    'type'            => "mark_paid",
                    'response'        => json_encode(['message' => "Order has been marked as paid manually from the order details page."]),
                    'status'          => 1,
                ]);

                session()->flash("success", "Order has been marked as paid successfully.");
            }
        } else {
            session()->flash("error", "Order not found.");
        }

        return back();
    }
    // GET /orders/{order}/packages-json
    public function getPackagesJson(Order $order)
    {
        // Fetch packages and group them by dimensions and weight
        $packages = $order->packages()
            ->select('length', 'breadth', 'height', 'dead_weight', DB::raw('COUNT(*) as package_count'))
            ->groupBy('length', 'breadth', 'height', 'dead_weight')
            ->get();

        return response()->json([
            'packages' => $packages
        ], 200);
    }

    // POST /orders/{order}/packages
    public function storePackages(Request $request, Order $order)
    {
        $validated = $request->validate([
            'packages' => 'required|array|min:1',
            'packages.*.package_count' => 'required|integer|min:1|max:25',
            'packages.*.length'        => 'required|numeric|min:0',
            'packages.*.breadth'       => 'required|numeric|min:0',
            'packages.*.height'        => 'required|numeric|min:0',
            'packages.*.dead_weight'   => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Delete existing packages for this order
            $order->packages()->delete();

            $allPackages = [];
            $packageCode = 1;

            foreach ($validated['packages'] as $pkg) {
                for ($i = 1; $i <= $pkg['package_count']; $i++) {
                    $allPackages[] = [
                        'package_code' => $packageCode++,
                        'length'       => $pkg['length'],
                        'breadth'      => $pkg['breadth'],
                        'height'       => $pkg['height'],
                        'dead_weight'  => $pkg['dead_weight'],
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];
                }
            }

            // Bulk insert packages for this order
            $order->packages()->createMany($allPackages);

            // Update order package summary (for first package)
            $firstPackage = $validated['packages'][0];
            $order->update([
                'package_type'     => count($allPackages) > 1 ? 'MPS' : 'SPS',
                'package_length'   => $firstPackage['length'],
                'package_breadth'  => $firstPackage['breadth'],
                'package_height'   => $firstPackage['height'],
                'package_dead_weight' => $firstPackage['dead_weight'],
            ]);

            DB::commit();

            session()->flash("success", "Packages updated successfully for Order ID: " . $order->vendor_order_number);
            return response()->json(['message' => 'Packages updated successfully'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            session()->flash("error", "Failed to update packages for Order ID: " . $order->vendor_order_number);
            return response()->json(['message' => 'Failed to update packages', 'error' => $e->getMessage()], 500);
        }
    }


}

