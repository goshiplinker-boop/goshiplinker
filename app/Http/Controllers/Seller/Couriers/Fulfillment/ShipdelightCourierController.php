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
class ShipdelightCourierController extends Controller
{
    private $order_ids 		= array();
	private $courier_id 		= 0;
	private $company_id 		= 0;
    private $courier_settings 	= array();
    private $api_mode = '';
    private $api_key ='';
    private $env_type='';
    private $api_url='';
    public $pickup_address = array();
    public $return_address = array();
    public $errors = array();
    public $result=array();
    public $action="";
    public $print_response=array();
    public $shipment_mode='';
    public $parent_courier_id=15;
    public function __construct($order_ids = array() , $courier_id = 0 , $company_id = 0,$courier_settings=array()){
		
		$this->order_ids 	= $order_ids;
		$this->courier_id 	= $courier_id;
		$this->company_id 	= ($company_id) ? $company_id : session('company_id');
        $this->courier_settings = $courier_settings;
        $courier_details = ($courier_settings->courier_details)?json_decode($courier_settings['courier_details'],true):array();
        foreach($courier_details as $key=>$value){
            $this->$key = $value;
        }    
        if($this->api_mode=='dev'){
            $this->api_url = 'https://appapi-dev.shipdelight.com';

        }  else {
            $this->api_url = 'https://appapi.shipdelight.com';
        }
       
       
		
	}
	public function assignTrackingNumber()
    {
        $token = $this->authentication();
        if (!$token) {
            $this->result['error'][] = 'Failed to authenticate with Shipdelight API.';
            return $this->result;
        }
        $orderService =app(OrderService::class);
        $shipmentService = app(OrderShipmentService::class);
        foreach ($this->order_ids as $order_id) {
            $order_info = Order::with('orderProducts', 'orderTotals', 'shipmentInfo')->find($order_id)->toArray();

            if (isset($order_info['shipment_info']) && !empty($order_info['shipment_info'])) {
                $this->result['error'][$order_info['vendor_order_number']] = "Tracking number already assigned";
                continue;
            }
            $giftwrap_charges = 0;
            $shipping_charges = 0;
            $discount = 0;
            $cod_charges = 0;
            $advanced_paid = 0;
            if(!empty($order_info['order_totals'])){
                foreach($order_info['order_totals'] as $order_totals){
                    if($order_totals['code']=='giftwrap'){
                        $giftwrap_charges = $order_totals['value'];
                    }
                    if($order_totals['code']=='shipping'){
                        $shipping_charges = $order_totals['value'];
                    }
                    if($order_totals['code']=='discount'){
                        $discount = $order_totals['value'];
                    }
                    if($order_totals['code']=='cod_charges'){
                        $cod_charges = $order_totals['value'];
                    }
                    if($order_totals['code']=='advanced'){
                        $advanced_paid = $order_totals['value'];
                    }

                }

            }
            $order_info['payment_mode'] = strtolower($order_info['payment_mode']); 
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
            $orderProducts = $order_info['order_products'];
            $shipment_items = [];
            foreach($orderProducts as $key=>$orderProduct){
                $shipment_items[] =[
                    'name' => $orderProduct['product_name'],
                    'quantity' => $orderProduct['quantity'],
                    'sku' => $orderProduct['sku'],
                    'product_id' => '',
                    'variant_id' => '',
                    'unit_price' => $orderProduct['unit_price'],
                    'actual_weight' => '0.05',
                    'item_color' => '',
                    'item_size' => '',
                    'item_category' => '',
                    'item_image' => '',
                    'item_brand' => '',
                    'item_imei' => '',
                    'special_ins' => '',
                    'return_reasons' => '',
                    'item_tag' => '',
                    'item_box' => '',
                ];
            }
            $package_dead_weight = $order_info['package_dead_weight']??0.05;
            $volumetric_weight = $order_info['package_length']*$order_info['package_breadth']*$order_info['package_height']/5000;
            // Prepare package information
            $order_date = $order_info['channel_order_date'];
            $order_date = Carbon::parse($order_date)->format('Y-m-d');
            $packageInfo = [
                'auto_approve' => true,
                'order_number' => $order_info['vendor_order_number']?:$order_info['id'],
                'service_type' => 'f',
                'invoice_number' =>$order_info["invoice_prefix"].$order_info["invoice_number"],
                'transaction_ref_no' => '',
                'vendor_code' => $this->vendor_code ?? '',
                'payment_method' => ($order_info['payment_mode'] === 'prepaid') ? 'PPD' : 'COD',
                'discount_total' => $discount,
                'cod_shipping_charge' => $shipping_charges,
                'invoice_total' => $order_info['order_total'],
                'cod_total' => ($order_info['payment_mode'] === 'cod') ? $order_info['order_total'] : 0.00,
                'manual_courier' => $this->shipdelight_courier_id??'',
                'length' => $order_info['package_length'] ?? '10',
                'breadth' => $order_info['package_breadth'] ?? '10',
                'height' => $order_info['package_height'] ?? '10',
                'actual_weight' => $package_dead_weight,
                'volumetric_weight' => $volumetric_weight,
                'qc' => 'n',
                'forward_ref' => '',
                'shipping' => [
                    'first_name' => $order_info['s_fullname'] ?: $order_info['b_fullname'],
                    'last_name' => '',
                    'address_1' => $order_info['s_complete_address'] ?: $order_info['b_complete_address'],
                    'address_2' =>$order_info['s_landmark'] ?: $order_info['b_landmark'],
                    'city' => $order_info['s_city'] ?: $order_info['b_city'],
                    'state' => $order_info['s_state'] ?: $order_info['b_state'],
                    'postcode' =>$order_info['s_zipcode'] ?: $order_info['b_zipcode'],
                    'country' => $order_info['s_country'] ?: $order_info['b_country'],
                    'phone' => $consignee_phone,
                    'cust_email' => $order_info['email'] ??'',
                ],
                'line_items' =>$shipment_items,
                'pickup' => [
                    'vendor_name' => $this->pickup_address->location_title,
                    'vendor_code' => $this->vendor_code ?? '',
                    'address_1' => $this->pickup_address->address,
                    'address_2' => $this->pickup_address->landmark,
                    'city' => $this->pickup_address->city,
                    'state' => $this->pickup_address->state,
                    'postcode' => $this->pickup_address->zipcode,
                    'country' => $this->pickup_address->country,
                    'phone' =>$this->pickup_address->phone,
                ],
                'rto' => [
                    'vendor_name' => $this->return_address->location_title,
                    'vendor_code' => $this->vendor_code ?? '',
                    'address_1' => $this->return_address->address,
                    'address_2' => $this->return_address->landmark,
                     'city' => $this->return_address->city,
                    'state' => $this->return_address->state,
                    'postcode' => $this->return_address->zipcode,
                    'country' => $this->return_address->country,
                    'phone' =>$this->return_address->phone,
                ],
                'gst_details' => [
                    'gst_number' => $this->pickup_address->gstin,
                    'cgst' => '',
                    'igst' => '',
                    'sgst' => '',
                    'hsn_number' => '',
                    'ewaybill_number' => '',
                ],
            ];
            
                  
            if (ShipmentInfo::where('order_id', $order_id)->exists()) {
                $this->result['error'][$order_info['vendor_order_number']] = 'Tracking number already assigned';
                continue;
            }
            $apiUrl = $this->api_url.'/booking';
            $headers = [
                'Authorization' => 'Bearer '.$token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ];
            $response = Http::withHeaders($headers)->post($apiUrl,$packageInfo);
            $response = $response->json(); 
            $data = $response['data']??[];
          
            $status = $data['success']??false;
            $courier_response = $data['response']??[];
            $trackingNumber = $courier_response['airwaybilno']??'';
            
            $pickup_address = $this->pickupAddressFormat($this->pickup_address);
            $return_address = $this->pickupAddressFormat($this->return_address);
            $courier_name = $courier_response['courier']??'';
            $routing_code = $courier_response['routing_code']??'';
            $label_url = $courier_response['dispatch_label']??'';
            if ($status && $trackingNumber) {     
                $other_details = array();    
                $other_details['label_url'] = $label_url;
                $other_details['destination_area'] = $routing_code;
                 try{
                    DB::transaction(function () use (
                            $order_id,
                            $order_info,
                            $trackingNumber,
                            $courier_name,
                            $other_details,
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
                                'tracking_id' => $trackingNumber,
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
                                'courier_code'    => 'shipdelight',
                                'tracking_number' => $trackingNumber,
                                'amount'          => $rate['shipping_cost'],
                                'cod_charges'     => $rate['cod_charge'] ?? 0
                            ]);
                            
                            /** -------------------------------
                            * STEP 3: Update Order Status
                            * -------------------------------- */
                            Order::where('id', $order_id)->update(['status_code' => 'P', 'rate_card_id' => $rate['rate_card_id']??null]);
                            OrderCourierResponse::create([
                                    'order_id' => $order_id,
                                    'courier_code' => 'shipdelight',
                                    'courier_name' => $courier_name,
                                    'response' => $other_details,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);  

                    });  
                    $this->result['success'] = true;
                } catch (\Exception $e) {
                        Log::error("Error processing order ID $order_id: " . $e->getMessage());
                        $this->result['error'][$order_info['vendor_order_number']] = "Failed to assigned tracking number"." Error: ".$e->getMessage();
                        continue;
                }
            } else {    
                $message = $courier_response['message'] ?? 'Unknown error occurred';   
                $this->result['error'][$order_info['vendor_order_number']] = "Failed to assign tracking number. Error: " .  $message;

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
    public function authentication(){
        $company_id = $this->company_id;
        $token = Cache::get("api_auth_token_shipdelight_{$this->courier_id}_{$company_id}");
        $url = $this->api_url."generate-token?api_key={$this->api_key}";
        if (!$token) {
            $response = Http::withHeaders([
                'Accept' => 'application/json', 
            ])->post($url);        
            $response = $response->json();
            $token = $response['data']['access_token']??'';
            if($token){
                Cache::put("api_auth_token_shipdelight_{$this->courier_id}_{$company_id}", $token, now()->addMinutes(59));
            }
            if($this->action == 'print_response'){
                $this->print_response['authentication']['auth_url'] = $url;
				$this->print_response['authentication']['auth_header']=[
                    'Accept' => 'application/json',
                  
                ];
                $this->print_response['authentication']['auth_response'] = $response;
			}
        }
        
        return $token;
    }
    
    public function trackShipment($order_id, $tracking_number)
    {
        $token = $this->authentication();
        if (!$token) {
            return ['error' => 'Failed to authenticate with Shipdelight API.'];
        }
        $parentId = $this->courier_settings->courier?->parent_id;
        $orderService = new OrderService();
        $payload = [
            "filter_type"=>"airwaybilno",
            "filter_value"=>$tracking_number
        ];
        $url = $this->api_url . '/tracking';
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'   => 'application/json',
            'Accept' => 'application/json',
        ];

        try {
            $order = Order::with('shipmentInfo')
            ->where('id', $order_id)
            ->first();
            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->post($url, $payload);

            if (!$response->ok()) {
                return ['error' => "HTTP request failed with status: {$response->status()}"];
            }

            $data = $response->json();
            $status = $data['success'] ?? false;
            $shipmentData = $data['tracking'][0] ?? [];

            if (!$status || empty($shipmentData)) {
                return ['error' => $message ?: 'Shipment tracking data not found'];
            }
            $latest_status = $shipmentData['latest_status'] ?? '';
            $edd = $shipmentData['exp_delivery'] ?? '';

            $scans = $shipmentData['tracking_history'] ?? [];
           

            // Build tracking data
            $scansdata = [
                'courier_id'             => $this->courier_id,
                'tracking_number'        => $tracking_number,
                'origin'                 =>  '',
                'destination'            => '',
                'pickup_date'            => '',
                'expected_delivery_date' => $edd,
                'pod'                    => '',
                'scans'                  => [],
            ];

            $courier_status_mapping = CourierStatusMapping::firstOrCreate(
                [
                    'courier_id'     => $parentId,
                    'courier_status' => $latest_status
                ],
                [
                    'shipment_status_code' => '',
                ]
            );

            $scansdata['current_status_code'] = $courier_status_mapping->shipment_status_code ?: $latest_status;
            $scansdata['current_status_date'] = '';

           
            foreach ($scans as $scan) {
                $activity = $scan['status'] ?? '';
                $location = $scan['location'] ?? '';
                $date = $scan['updated_at'] ?? '';

                $scan_mapping = CourierStatusMapping::firstOrCreate(
                    [
                        'courier_id'     => $parentId,
                        'courier_status' => $scan['lsp_status'] ?? $activity
                    ],
                    [
                        'shipment_status_code' => '',
                    ]
                );
                if($activity=='PICKUP DONE'){
                    $scansdata['pickup_date'] = $date;
                }
                $scansdata['current_status_date'] = $date;
                $scansdata['scans'][] = [
                    'status'              => $activity,
                    'current_status_code' => $scan_mapping->shipment_status_code ?: $activity,
                    'date'                => $date,
                    'location'            => $location,
                ];
            }
            
            if($scansdata){
                $orderService->addShipmentTrackDetails($order_id,$scansdata);
            }
            return $scansdata;

        } catch (\Throwable $e) {
            return ['error' => 'Exception: ' . $e->getMessage()];
        }
    }
    public function cancelShipments() {
        $this->result = ['success' => false, 'error' => [], 'message' => ''];
        $token = $this->authentication();
        if (!$this->order_ids || count($this->order_ids) == 0) {
            $this->result['error'][] = 'No order IDs found.';
            return response()->json($this->result);
        }
        $orders = Order::with('shipmentInfo')
            ->whereIn('id', $this->order_ids)
            ->get();

        foreach ($orders as $order) {
            $shipment = $order->shipmentInfo;

            if (!$shipment || !$shipment->tracking_id) {
                $this->result['error'][] = "Tracking ID not found for Order ID {$order->id}";
                continue;
            }
            $url = $this->api_url . '/cancel_pickup';
            try {
                $payload = [
                     "airwaybillno" => $shipment->tracking_id
                ];
                $headers = [
                    'Content-Type'   => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ];
                $response = Http::withHeaders($headers)->post($url, $payload);
                

                $responseData = $response->json();
                $status  = $responseData['success'] ?? false;
                $message = $responseData['message'] ?? 'Unknown API response';
                if ($status) {
                    app(OrderShipmentService::class)->cancelOrderById($order->id);
                    $this->result['success'][] = "Order ID $order->vendor_order_number canceled successfully.";
                } else {
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