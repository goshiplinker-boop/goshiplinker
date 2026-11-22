<?php

namespace App\Http\Controllers\Seller\Couriers\Fulfillment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Pickup;
use App\Models\ShipmentInfo;
use App\Services\OrderService;
use App\Models\CourierStatusMapping;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\OrderCourierResponse;
use Illuminate\Support\Facades\Cache;
use App\Services\ShippingRateService;
use App\Services\SellerWalletService;
use App\Services\OrderShipmentService;
use Carbon\Carbon;
class ShadowfaxCourierController extends Controller
{
    private $order_ids 		= array();
	private $courier_id 		= 0;
	private $company_id 		= 0;
    private $courier_settings 	= array();
    private $api_mode = '';
    private $token ='';
    private $env_type='';
    private $api_url='';
    public $pickup_address = array();
    public $return_address = array();
    public $errors = array();
    public $result=array();
    public $action="";
    public $print_response=array();
    public $shipment_mode='';
    public $force_cancel=0;
    private int $parent_company_id;
    private int $parent_courier_id;
    public function __construct($order_ids = array() , $courier_id = 0 , $company_id = 0,$courier_settings=array()){
		
		$this->order_ids 	= $order_ids;
		$this->courier_id 	= $courier_id;
		$this->company_id 	= ($company_id) ? $company_id : session('company_id');
        $this->courier_settings = $courier_settings;
        $this->parent_company_id = $courier_settings['company_id'] ?? 0;
        $this->parent_courier_id = 14;
        $courier_details = ($courier_settings->courier_details)?json_decode($courier_settings['courier_details'],true):array();
        foreach($courier_details as $key=>$value){
            $this->$key = $value;
        }  
        if($this->env_type =='dev'){
            $this->api_url = 'https://dale.staging.shadowfax.in/api';            
        }else{
            $this->api_url = 'https://dale.shadowfax.in/api';            
        }
        

              
		
	}
	public function assignTrackingNumber()
    {
        $orderService =app(OrderService::class);
        $shipmentService = app(OrderShipmentService::class);
        foreach ($this->order_ids as $order_id) {
            $order_info = Order::with('orderProducts', 'orderTotals', 'shipmentInfo')->find($order_id)->toArray();

            if (isset($order_info['shipment_info']) && !empty($order_info['shipment_info'])) {
                $this->result['error'][$order_info['vendor_order_number']] = "Tracking number already assigned";
                continue;
            }
            
            $payment_mode = strtolower($order_info['payment_mode']); 
             $rateService = app(ShippingRateService::class);

            $weight = $order_info['package_dead_weight'] ?? 0.5;
            $isCod  = $order_info['payment_mode'] === 'cod';

            $rate = $rateService->calculate(
                $this->parent_company_id,
                $this->company_id,
                $this->courier_id,
                $this->parent_courier_id,
                $this->pickup_address->zipcode,
                $order_info['s_zipcode'] ?: $order_info['b_zipcode'],
                $weight,
                $order_info['package_length'] ?? 10,
                $order_info['package_breadth'] ?? 10,
                $order_info['package_height'] ?? 10,
                (int)$isCod,
                $order_info['order_total']
            );
            if (!isset($rate['shipping_cost'])) {
                $this->result['error'][$order_info['vendor_order_number']] = "Pincode is not serviceble";
                continue;
            }
            $courier_shipping_cost = $rate['shipping_cost'];
            $cod_charges = $isCod ? ($rate['cod_charge'] ?? 0) : 0;
            $walletService = app(SellerWalletService::class);
            if (!$walletService->hasSufficientBalance($this->company_id,$courier_shipping_cost)) {
                $this->result['error'][$order_info['vendor_order_number']] = 'Insufficient wallet balance to ship this order';
                continue;
            }
            $consignee_phone = isset($order_info['s_phone']) ? $order_info['s_phone'] : $order_info['b_phone'];
            $consignee_phone = preg_replace('/[^0-9]/', '', $consignee_phone);
            $consignee_phone = substr($consignee_phone, -10);
            
            
            // Prepare package information
            $package_dead_weight = $order_info['package_dead_weight'] ?? 0.05;
            $s_complete_address = $order_info['s_complete_address'] ?: $order_info['b_complete_address'];
            $landmark = $order_info['s_landmark'] ?: $order_info['s_landmark'];
            if($landmark){
                $s_complete_address .= ','.$landmark;
            }
            $consignee_phone = isset($order_info['s_phone']) ? $order_info['s_phone'] : $order_info['b_phone'];
            $consignee_phone = preg_replace('/[^0-9]/', '', $consignee_phone);
            $consignee_phone = substr($consignee_phone, -10);
            $orderProducts = $order_info['order_products'];
            $shipment_items = [];
            foreach($orderProducts as $key=>$orderProduct){
                $shipment_items[] = [
                    "hsn_code" => $orderProduct['hsn'],
                    "invoice_no" => "",
                    "sku_name" => $orderProduct['sku'],
                    "sku_id" => "",
                    "category" => "",
                    "price" => $orderProduct['unit_price'],
                    "seller_details" => [
                        "seller_name" => "",
                        "seller_address" => "",
                        "seller_state" => "",
                        "gstin_number" => ""
                    ],
                    "taxes" => [
                        "cgst" => 0,
                        "sgst" => 0,
                        "igst" => 0,
                        "total_tax" =>0
                    ],
                    "additional_details" => [
                        "requires_extra_care" => "False",
                        "type_extra_care" => "",
                        "quantity" => $orderProduct['quantity'],
                    ]
                ];

            }
            $packageInfo = [
                    "order_type" => "marketplace",
                    "order_details" => [
                        "client_order_id" => $order_info['vendor_order_number']??$order_info['id'],
                        "actual_weight" => $package_dead_weight,
                        "volumetric_weight" => 100,
                        "product_value" => 515,
                        "payment_mode" => ($payment_mode=='prepaid')?"Prepaid":"COD",
                        "cod_amount" => ($payment_mode=='prepaid')?0:$order_info['order_total'],
                        "total_amount" => $order_info['order_total'],
                        "eway_bill" => "",
                        "gstin_number" => "",
                        "order_service" => "regular"
                    ],

                    "customer_details" => [
                        "name" => $order_info['s_fullname']?:$order_info['b_fullname'],
                        "contact" => $consignee_phone,
                        "address_line_1" => $order_info['s_complete_address']?:$order_info['b_complete_address'],
                        "address_line_2" => $order_info['s_landmark']?:$order_info['b_landmark'],
                        "city" =>$order_info['s_city']?:$order_info['b_city'],
                        "state" => $order_info['s_state_code']?:$order_info['b_state_code'],
                        "pincode" => $order_info['s_zipcode']?:$order_info['b_zipcode'],
                        "alternate_contact" => $order_info['b_phone']?:$order_info['s_phone'],
                        "latitude" => "",
                        "longitude" => ""
                    ],

                    "pickup_details" => [
                        "name" => $this->pickup_address->contact_person_name,
                        "contact" => $this->pickup_address->phone,
                        "address_line_1" =>$this->pickup_address->address,
                        "address_line_2" =>$this->pickup_address->landmark,
                        "city" =>$this->pickup_address->city,
                        "state" =>$this->pickup_address->state_code,
                        "pincode" =>$this->pickup_address->zipcode,
                        "latitude" => "",
                        "longitude" => "",
                        "unique_code" =>$this->pickup_address->location_title,
                    ],

                    "rts_details" => [
                         "name" => $this->return_address->contact_person_name,
                        "contact" => $this->return_address->phone,
                        "address_line_1" =>$this->return_address->address,
                        "address_line_2" =>$this->return_address->landmark,
                        "city" =>$this->return_address->city,
                        "state" =>$this->return_address->state_code,
                        "pincode" =>$this->return_address->zipcode,
                        "latitude" => "",
                        "longitude" => "",
                        "unique_code" =>$this->return_address->location_title,
                    ],

                    "product_details" => $shipment_items
                ];
            
            
            
            if (ShipmentInfo::where('order_id', $order_id)->exists()) {
                $this->result['error'][$order_info['vendor_order_number']] = 'Tracking number already assigned';
                continue;
            }      
            $apiUrl = $this->api_url.'/v3/clients/orders/';
            $headers = [
                'Authorization' => 'Token '.$this->api_token,
                'Content-Type'  => 'application/json',
            ];
           
            $response = Http::withHeaders($headers)->post($apiUrl,$packageInfo);
            $response = $response->json(); 
            $status = $response['message']??'';
            $error = $response['errors']??$status;
            if(!empty($error) && is_array($error)){
                $error = json_encode($error);
            }
            
            $payload = $response['data']??[];     
            $tracking_number = $payload['awb_number']??'';
            if ($status=='Success' && !empty($tracking_number)) {  
                $pickup_address = $this->pickupAddressFormat($this->pickup_address);
                $return_address = $this->pickupAddressFormat($this->return_address);
                try{
                    DB::transaction(function () use (
                            $order_id,
                            $order_info,
                            $tracking_number,
                            $pickup_address,
                            $return_address,
                            $rate
                        ) {
                            /** -------------------------------
                            * STEP 1: Create Shipment
                            * -------------------------------- */
                            
                            $shipment =  ShipmentInfo::firstOrCreate([
                                'order_id' => $order_id],[
                                'company_id' => $this->company_id,
                                'shipment_type' => '',
                                'courier_id' => $this->courier_id,
                                'tracking_id' => $tracking_number,
                                'applied_weight' => $rate['chargeable_weight']??0,
                                'pickedup_location_id' => $this->pickup_address->id,
                                'pickedup_location_address' => $pickup_address,
                                'return_location_id' => $this->return_address->id,
                                'return_location_address' => $return_address,
                                'manifest_created' => 0,
                                'payment_mode' => $order_info['payment_mode'],
                            ]);
                            /** -------------------------------
                             * STEP 2: Apply Freight (Wallet Debit)
                             * -------------------------------- */
                            app(SellerWalletService::class)->applyFreight([
                                'company_id'      => $this->company_id,
                                'order_id'        => $order_id,
                                'shipment_id'     => $shipment->id,
                                'courier_id'      => $this->courier_id,
                                'courier_code'    => 'shadowfax',
                                'tracking_number' => $tracking_number,
                                'amount'          => $rate['shipping_cost'],
                                'cod_charges'     => $rate['cod_charge'] ?? 0
                            ]);
                            
                            /** -------------------------------
                            * STEP 3: Update Order Status
                            * -------------------------------- */
                            Order::where('id', $order_id)->update(['status_code' => 'P', 'rate_card_id' => $rate['rate_card_id']??null]);
                    });  
                    $this->result['success'] = true;
                } catch (\Exception $e) {
                        Log::error("Error processing order ID $order_id: " . $e->getMessage());
                        $this->result['error'][$order_info['vendor_order_number']] = "Failed to assigned tracking number"." Error: ".$e->getMessage();
                        continue;
                }  
                                   
                
            } else {
                $this->result['error'][$order_info['vendor_order_number']] = "Failed to assigned tracking number"." : $error";
            }
            if($this->action == 'print_response'){
                 $this->print_response['assign']['url'] = $apiUrl;
                $this->print_response['assign']['header']=$headers;
                $this->print_response['assign']['request_data']=$packageInfo;
                $this->print_response['assign']['response_data'] = $response;
                $this->result['print_response'] = $this->print_response;
                return $this->result;
            }
        }

        return $this->result;
    }   
    
    public function trackShipment($order_id,$tracking_number){
        $order_info = Order::find($order_id);
        $vendor_order_number = $order_info->vendor_order_number??0;
        $parentId = $this->courier_settings->courier?->parent_id;
        
        $orderService =new OrderService();
        $url = $this->api_url.'/v4/clients/orders/'.$tracking_number.'/track/';
        $headers = [
            'Authorization' => 'Token '.$this->api_token,
            'Content-Type'  => 'application/json',
        ];
        
        $response = Http::withHeaders($headers)->get($url);
        $response = $response->json();   
           
        $status = $response['message']??'';
        $shipmentData = $response['order_details']??[];
        $scansdata = array();   
        if($status =='Success' && !empty($shipmentData)){ 
            $current_status = $shipmentData['status'];
            $pickup_details = $shipmentData['pickup_details']??[];
            $delivery_details = $shipmentData['delivery_details']??[];
            $scansdata['courier_id'] = $this->courier_id;  
            $scansdata['tracking_number'] = $tracking_number;  
            $scansdata['origin'] = $pickup_details['city']??'';                
            $scansdata['destination'] = $delivery_details['city']??'';
            $scansdata['pickup_date'] =  '';
            $estimated_delivery = $shipmentData['promised_delivery_date']??'';   
            
            $scansdata['expected_delivery_date'] =  ($estimated_delivery)? Carbon::parse($estimated_delivery)->format('Y-m-d H:i:s'):'';  
            $scansdata['pod'] = '';                          
            $scansdata['scans'] = [];       
            $courier_status_mapping = CourierStatusMapping::where('courier_id', $parentId)
                    ->where('courier_status', $current_status)
                    ->first();
                    if (!$courier_status_mapping) {
                        $courier_status_mapping = CourierStatusMapping::create([
                                'courier_id' => $parentId,
                                'courier_status' => $current_status,
                                'shipment_status_code' => '',
                            ]
                        );
                    }     
            $scansdata['current_status_code'] = ($courier_status_mapping->shipment_status_code)?$courier_status_mapping->shipment_status_code:$current_status;
                        
            $scans = $response['tracking_details']??[];
            if($scans){
                foreach($scans as $ScanDetail){
                    $trackingHistories = array();
                    
                    $trackingHistories['status'] = $ScanDetail['status']; 
                    $courier_status_mapping = CourierStatusMapping::where('courier_id', $parentId)
                    ->where('courier_status', $ScanDetail['status_id'])
                    ->first();
                    if (!$courier_status_mapping) {
                        $courier_status_mapping = CourierStatusMapping::create([
                                'courier_id' => $parentId,
                                'courier_status' => $ScanDetail['status_id'],
                                'shipment_status_code' => '',
                            ]
                        );
                    }     
                    $shipment_status_code = ($courier_status_mapping->shipment_status_code)?$courier_status_mapping->shipment_status_code:$ScanDetail['status_id'];
                    
                    $trackingHistories['current_status_code'] = $shipment_status_code;              
                    $scansdata['current_status_date'] = $trackingHistories['date'] = Carbon::parse($ScanDetail['created'])->format('Y-m-d H:i:s');
                    $trackingHistories['location'] = $ScanDetail['location']??''; 
                    if($ScanDetail['status_id']=='picked'){
                        $scansdata['pickup_date'] = $trackingHistories['date'];
                    }
                    $scansdata['scans'][] = $trackingHistories;                        
                }
            }
            
        }
        //\Log::error(json_encode($scansdata));
        if($scansdata){
            $orderService->addShipmentTrackDetails($order_id,$scansdata);
        }
        return $scansdata;

    }
    public function cancelShipments() {
        $this->result = ['success' => false, 'error' => [], 'message' => ''];

        if (!$this->order_ids || count($this->order_ids) == 0) {
            $this->result['error'][] = 'No order IDs found.';
            return response()->json($this->result);
        }
        $orders = Order::with('shipmentInfo')->whereIn('id', $this->order_ids)->get();

        foreach ($orders as $order) {
            $shipment = $order->shipmentInfo;

            if (!$shipment || !$shipment->tracking_id) {
                $this->result['error'][] = "Tracking ID not found for Order ID {$order->id}";
                continue;
            }
            $url = $this->api_url . '/v3/clients/orders/cancel/';
            try {
                $payload = [
                    "request_id"=> $shipment->tracking_id,
                    "cancel_remarks"=> "Request cancelled by customer",
                ];
                $headers = [
                    'Content-Type'   => 'application/json',
                    'Authorization' => 'Token '.$this->api_token,
                ];
                $response = Http::withHeaders($headers)->post($url, $payload);
                $responseData = $response->json();
                $status  = $responseData['responseCode'] ?? '';
                $message = $responseData['responseMsg'] ?? 'Unknown API response';
                if ($status==200) {
                    app(OrderShipmentService::class)->cancelOrderById($order->id);
                    $this->result['success'][] = "Order ID $order->vendor_order_number canceled successfully.";
                } else {
                    if($this->force_cancel==1){
                        app(OrderShipmentService::class)->cancelOrderById($order->id);
                    }
                    $this->result['error'][] = "Order ID $order->vendor_order_number: $message";
                }

            } catch (\Exception $e) {
                $this->result['error'][] = "API error for Order ID {$order->vendor_order_number}: " . $e->getMessage();
            }
        }
        return $this->result;
    }
    public function pickupAddressFormat($pickup_address){
        $pick_address = $pickup_address->address;
        if($pickup_address->landmark){
            $pick_address .= ', '.$pickup_address->landmark;
        }
        if($pickup_address->city){
            $pick_address .= ', '.$pickup_address->city;
        }
        if($pickup_address->state_code){
            $pick_address .= ', '.$pickup_address->state_code;
        }
        if($pickup_address->zipcode){
            $pick_address .= ', '.$pickup_address->zipcode;
        } 
        return $pick_address;

    }
}
