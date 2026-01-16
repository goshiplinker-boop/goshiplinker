<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderTotal;
use App\Models\TrackingHistory;
use App\Models\PaymentMapping;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Jobs\Shipmentmail;
use App\Jobs\SendSmsjob;
use App\Models\Notification;
use App\Models\SmsGateway;
use App\Models\OrderPackage;
use App\Models\CourierMapping;
use App\Models\ShipmentInfo;
class OrderService
{
    public function createOrder($data, $companyId)
    {
        // Start a DB transaction to ensure atomicity
        DB::beginTransaction();

        try {
            // Check if an order with the same vendor_order_id, company_id, and channel_id already exists
            // $existingOrder = Order::where('vendor_order_id', $data['vendor_order_id'])
            //     ->where('company_id', $companyId)
            //     ->where('channel_id', $data['channel_id'])
            //     ->first();

            // if ($existingOrder) {
            //     DB::rollBack();
            //     return ['status' => 'error', 'message' => 'Order already exists','is_exist'=>1];
            // }
            // Get or create the customer
            $data['phone_number'] =(!empty($data['phone_number']))?substr(str_replace(" ", "",$data['phone_number']), -10):'';
            $data['s_phone'] = (!empty($data['s_phone']))?substr(str_replace(" ", "",$data['s_phone']), -10):'';
            $data['b_phone'] = (!empty($data['b_phone']))?substr(str_replace(" ", "",$data['b_phone']), -10):'';
            $email = $data['email']??'';
            $customer_id=0;
            if($email){            
                $customer = Customer::firstOrCreate(
                    [
                        'company_id' => $companyId,
                        'email_id' => $data['email'],
                        'phone' => $data['phone_number']
                    ],
                    [
                        'fullname' => $data['fullname']
                    ]
                );
                $customer_id = $customer->id??0;
            }
            $data['payment_mode'] = !empty($data['payment_mode'])?strtolower($data['payment_mode']):null;
            // Create the order
            $order = Order::firstOrCreate(
                [
                'vendor_order_number' => $data['vendor_order_number'],
                'channel_id' => $data['channel_id'],
                'company_id' => $companyId
                ],
                [
                'vendor_order_id' => $data['vendor_order_id'],
                'vendor_order_number' => $data['vendor_order_number'],
                'channel_id' => $data['channel_id'],
                'company_id' => $companyId,
                'customer_id' => $customer_id,
                'channel_order_date' => $data['channel_order_date'],
                'fullname' => $data['fullname']??null,
                'email' => $email,
                'phone_number' => $data['phone_number']??null,
                's_fullname' => $data['s_fullname']??null,
                's_company' => $data['s_company'] ?? null,
                's_complete_address' => $data['s_complete_address'],
                's_landmark' => $data['s_landmark'] ?? null,
                's_phone' => $data['s_phone']??null,
                's_zipcode' => $data['s_zipcode']??null,
                's_city' => $data['s_city']??null,
                's_state_code' => $data['s_state_code']??null,
                's_country_code' => $data['s_country_code']??null,
                'b_fullname' => $data['b_fullname']??null,
                'b_company' => $data['b_company'] ?? null,
                'b_complete_address' => $data['b_complete_address']??null,
                'b_landmark' => $data['b_landmark'] ?? null,
                'b_phone' => $data['b_phone']??null,
                'b_zipcode' => $data['b_zipcode']??null,
                'b_city' => $data['b_city']??null,
                'b_state_code' => $data['b_state_code']??null,
                'b_country_code' => $data['b_country_code']??null,
                'invoice_prefix' => $data['invoice_prefix']??null,
                'invoice_number' => ($data['invoice_type']=='order_number')?$data['vendor_order_number']:null,
                'status_code' => 'N',
                'financial_status' => $data['financial_status']??null,
                'payment_mode' => $data['payment_mode']??null,
                'payment_method' => $data['payment_method']??null,
                'notes' => $data['notes'] ?? null,
                'order_tags' => $data['order_tags'] ?? null,
                'currency_code' => $data['currency_code'] ?? 'INR',
                'package_length' => $data['package_length'] ?? 10,
                'package_breadth' => $data['package_breadth'] ?? 10,
                'package_height' => $data['package_height'] ?? 10,
                'package_dead_weight' => $data['package_dead_weight'] ?? 0.05,
                'sub_total' => $data['sub_total'],
                'order_total' => $data['order_total'],
                'customer_ip_address' => $data['customer_ip_address']??null
            ]);
            if($data['invoice_type']=='custom_number'){                
                $lastInvoice = DB::table('orders')->where('company_id', $companyId)
                ->selectRaw('COALESCE(MAX(CAST(REGEXP_REPLACE(invoice_number, "[^0-9]", "") AS UNSIGNED)), 0) AS invoice_number')
                ->value('invoice_number');      
                $newInvoiceNumber = ($lastInvoice >= $data['invoice_start_from']) ? intval($lastInvoice) + 1 : $data['invoice_start_from'];
                $order->invoice_number = $newInvoiceNumber;
                $order->save();
            }
            $fulfillment_details = $data['fulfillment_details']??[];
            $fulfillment_id = $fulfillment_details['fulfillment_id']??null;
            
            // Create the order products
            foreach ($data['order_products'] as $product) {
                OrderProduct::create([
                    'order_id' => $order->id,
                    'product_name' => $product['product_name'],
                    'sku' => $product['sku'],
                    'line_item_id' => $product['line_item_id']??null,
                    'unit_price' => $product['unit_price'],
                    'quantity' => $product['quantity'],
                    'discount' => $product['discount'] ?? 0,
                    'shipping' => $product['shipping'] ?? 0,
                    'hsn' => $product['hsn']??null,
                    'tax_rate' => $product['tax_rate'] ?: 0,
                    'tax_type' => $product['tax_type'] ?? 0,
                    'tax_name' => $product['tax_name'] ?? null,
                    'tax_amount' => $product['tax_amount'] ?? 0,
                    'total_price' => $product['total_price'],
                    'fulfillment_id' => $fulfillment_id,
                ]);
            }

            // Create the order totals
            foreach ($data['order_totals'] as $total) {
                OrderTotal::create([
                    'order_id' => $order->id,
                    'title' => $total['title'],
                    'code' => $total['code'],
                    'value' => $total['value'],
                    'sort_order' => $total['sort_order'],
                ]);
            }
            OrderPackage::create([
                'order_id' => $order->id,
                'package_code'=>1,
                'length' => $data['package_length'] ?? 10,
                'breadth' => $data['package_breadth'] ?? 10,
                'height' => $data['package_height'] ?? 10,
                'dead_weight' => $data['package_dead_weight'] ?? 0.05,
            ]);
            if(!empty($fulfillment_details)){
                $tracking_id = $fulfillment_details['tracking_number']??'';
                $courier_id = $fulfillment_details['courier_id']??0; 
                $pickedup_location_id = $fulfillment_details['pickedup_location_id']??0;                    
                $shipment_type = '';
                ShipmentInfo::create([
                    'order_id' => $order->id,
                    'company_id' => $companyId,
                    'shipment_type' => $shipment_type,
                    'courier_id' => $courier_id,
                    'tracking_id' => $tracking_id,
                    'pickedup_location_id' => $pickedup_location_id,
                    'fulfillment_status' =>1,
                    'return_location_id' => $pickedup_location_id,
                    'manifest_created' => 1,
                    'payment_mode' => $data['payment_mode']??null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $order->status_code = 'S';
                $order->save();
            }

            DB::commit();
            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;  // Let the caller handle the exception
        }
    }
    public function updateOrder($orderId,$data,$companyId)
    {
        DB::beginTransaction();

        try {
            $order = Order::where('id', $orderId)
                ->where('company_id', $companyId)
                ->where('status_code', 'N')
                ->first();

            if (!$order || $order->status_code !='N') {
                throw new \Exception('Order not found or You can modified only new orders');
            }
            // Normalize phone numbers
            $data['phone_number'] = (!empty($data['phone_number'])) ? substr(str_replace(" ", "", $data['phone_number']), -10) : '';
            $data['s_phone'] = (!empty($data['s_phone'])) ? substr(str_replace(" ", "", $data['s_phone']), -10) : '';
            $data['b_phone'] = (!empty($data['b_phone'])) ? substr(str_replace(" ", "", $data['b_phone']), -10) : '';

            // Update or create customer
            $email = $data['email'] ?? '';
            $customer_id = 0;
            if ($email) {
                $customer = Customer::firstOrCreate(
                    [
                        'company_id' => $companyId,
                        'email_id' => $data['email'],
                        'phone' => $data['phone_number']
                    ],
                    ['fullname' => $data['fullname']]
                );
                $customer_id = $customer->id ?? 0;
            }


            // Update main order
            $order->update([
                'channel_id' => $data['channel_id'],
                'customer_id' => $customer_id,
                'fullname' => $data['fullname'] ?? null,
                'email' => $email,
                'phone_number' => $data['phone_number'] ?? null,
                's_fullname' => $data['s_fullname'] ?? null,
                's_company' => $data['s_company'] ?? null,
                's_complete_address' => $data['s_complete_address'],
                's_landmark' => $data['s_landmark'] ?? null,
                's_phone' => $data['s_phone'] ?? null,
                's_zipcode' => $data['s_zipcode'] ?? null,
                's_city' => $data['s_city'] ?? null,
                's_state_code' => $data['s_state_code'] ?? null,
                's_country_code' => $data['s_country_code'] ?? null,
                'b_fullname' => $data['b_fullname'] ?? null,
                'b_company' => $data['b_company'] ?? null,
                'b_complete_address' => $data['b_complete_address'] ?? null,
                'b_landmark' => $data['b_landmark'] ?? null,
                'b_phone' => $data['b_phone'] ?? null,
                'b_zipcode' => $data['b_zipcode'] ?? null,
                'b_city' => $data['b_city'] ?? null,
                'b_state_code' => $data['b_state_code'] ?? null,
                'b_country_code' => $data['b_country_code'] ?? null,
                'currency_code' => $data['currency_code'] ?? 'INR',
                'package_length' => $data['package_length'] ?? 10,
                'package_breadth' => $data['package_breadth'] ?? 10,
                'package_height' => $data['package_height'] ?? 10,
                'package_dead_weight' => $data['package_dead_weight'] ?? 0.05,
                'sub_total' => $data['sub_total'],
                'order_total' => $data['order_total'],
                'customer_ip_address' => $data['customer_ip_address'] ?? null,
            ]);

            // Update OrderPackage (create if not exists)
            $orderPackage = OrderPackage::firstOrNew(['order_id' => $order->id]);
            $orderPackage->fill([
                'package_code' => 1,
                'length' => $data['package_length'] ?? 10,
                'breadth' => $data['package_breadth'] ?? 10,
                'height' => $data['package_height'] ?? 10,
                'dead_weight' => $data['package_dead_weight'] ?? 0.05,
            ])->save();

            $orderProducts = DB::table('order_products')
            ->select('id', 'order_id', 'tax_name', 'line_item_id','tax_rate','tax_type','tax_amount')
            ->get()
            ->keyBy('id');

            // Remove existing order products & totals before inserting updated ones
            OrderProduct::where('order_id', $order->id)->delete();
            OrderTotal::where('order_id', $order->id)->delete();

            // Insert updated order products
            foreach ($data['products'] as $product) {
                $order_product_id = $product['order_product_id'] ?? 0;

                $line_item = $orderProducts->get($order_product_id);
                $line_item_id = $line_item->line_item_id ?? null; 
                $tax_name = $line_item->tax_name ?? null; 
                $tax_type = $line_item->tax_type ?? 0; 
                $tax_rate = $line_item->tax_rate ?? 0; 
                $tax_amount = $line_item->tax_amount ?? 0; 
                $hsn = $line_item->hsn ?? null; 
                OrderProduct::create([
                    'order_id' => $order->id,
                    'product_name' => $product['product_name'],
                    'sku' => $product['sku']??'',
                    'line_item_id' => $line_item_id,
                    'unit_price' => $product['unit_price'],
                    'quantity' => $product['quantity'],
                    'discount' => $product['discount'] ?? 0,
                    'shipping' => $product['shipping'] ?? 0,
                    'hsn' => $hsn,
                    'tax_rate' => $tax_rate,
                    'tax_type' => $tax_type,
                    'tax_name' => $tax_name,
                    'tax_amount' => $tax_amount,
                    'total_price' => $product['total_price']
                ]);
            }

            // Insert updated order totals
            foreach ($data['order_totals'] as $total) {
                OrderTotal::create([
                    'order_id' => $order->id,
                    'title' => $total['title'],
                    'code' => $total['code'],
                    'value' => $total['value'],
                    'sort_order' => $total['sort_order'],
                ]);
            }

            DB::commit();
            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateOrderAddress($existorder, $order)
    {   $order_id = $existorder->id??0;
        if($order_id){
            $neworder_shipping_address = $order['s_fullname'].$order['s_company'].$order['s_complete_address'].$order['s_landmark'].$order['s_zipcode'].$order['s_city'].$order['s_state_code'].$order['s_country_code'];
            $neworder_billing_address = $order['b_fullname'].$order['b_company'].$order['b_complete_address'].$order['b_landmark'].$order['b_zipcode'].$order['b_city'].$order['b_state_code'].$order['b_country_code'];
            $existorder_shipping_address = $existorder->s_fullname.$existorder->s_company.$existorder->s_complete_address.$existorder->s_landmark.$existorder->s_phone.$existorder->s_zipcode.$existorder->s_city.$existorder->s_state_code.$existorder->s_country_code;
            $existorder_billing_address = $existorder->b_fullname.$existorder->b_company.$existorder->b_complete_address.$existorder->b_landmark.$existorder->b_phone.$existorder->b_zipcode.$existorder->b_city.$existorder->b_state_code.$existorder->b_country_code;
            
            if($neworder_shipping_address != $existorder_shipping_address || $neworder_billing_address != $existorder_billing_address){            
                try {
                    // Find the order and update it
                    $orderupdate = Order::where('id', $order_id)->update([
                        's_fullname' => $order['s_fullname'] ?? null,
                        's_company' => $order['s_company'] ?? null,
                        's_complete_address' => $order['s_complete_address'] ?? null,
                        's_landmark' => $order['s_landmark'] ?? null,
                        's_phone' => $order['s_phone'] ?? null,
                        's_zipcode' => $order['s_zipcode'] ?? null,
                        's_city' => $order['s_city'] ?? null,
                        's_state_code' => $order['s_state_code'] ?? null,
                        's_country_code' => $order['s_country_code'] ?? null,
                        'b_fullname' => $order['b_fullname'] ?? null,
                        'b_company' => $order['b_company'] ?? null,
                        'b_complete_address' => $order['b_complete_address'] ?? null,
                        'b_landmark' => $order['b_landmark'] ?? null,
                        'b_phone' => $order['b_phone'] ?? null,
                        'b_zipcode' => $order['b_zipcode'] ?? null,
                        'b_city' => $order['b_city'] ?? null,
                        'b_state_code' => $order['b_state_code'] ?? null,
                        'b_country_code' => $order['b_country_code'] ?? null
                    ]);    
                    return $orderupdate;
                } catch (\Exception $e) {
                    throw $e;  // Let the caller handle the exception
                }
            }else{
                return $existorder;
            }
        }else {
          return false;
        }
        
    }
    public function isOrderAlreadyImported($vendor_order_number,$companyId,$channelId ) {
        $order = Order::where("vendor_order_number", $vendor_order_number)
        ->where("channel_id", $channelId)
        ->where("company_id", $companyId)        
        ->first();
        return $order;
    }
    public function getPaymentMethod($order, $paymentMappings, &$unmappedPaymentCount)
    {
        $paymentGateway = $order['paymentGateway'] ?? '';
        // if (!$paymentGateway) {
        //     return null;
        // }
        $paymentGateway = strtolower($paymentGateway);
        $paymentMapping = $paymentMappings[$paymentGateway] ?? null;
        if (!$paymentMapping) {
            PaymentMapping::updateOrCreate(
                [
                    'channel_id' => $order['channel_id'],
                    'company_id' => $order['company_id'],
                    'gateway_name' => $paymentGateway,
                ],
                [
                    'status' => 0,
                ]
            );

            $unmappedPaymentCount++;
            return null;
        }

        return $paymentMapping->status ? $paymentMapping->payment_mode : null;
    }
    public function getCourierId($order, $courierMappings, &$unmappedCourierCount)
    {
        $fulfillment_details = $order['fulfillment_details']??[];
        $courier_name = $fulfillment_details['courier_name'] ?? '';
       
        $courier_name = strtolower($courier_name);
        $courierMapping = $courierMappings[$courier_name] ?? null;
        if (!$courierMapping) {
            CourierMapping::updateOrCreate(
                [
                    'channel_id' => $order['channel_id'],
                    'company_id' => $order['company_id'],
                    'courier_name' => $courier_name,
                ],
                [
                    'status' => 0,
                ]
            );

            $unmappedCourierCount++;
            return null;
        }

        return $courierMapping->status ? $courierMapping->courier_id : 0;
    }
    public function shouldSkipOrderBasedOnTags($order, $setting)
    {
        if (empty($setting->other_details['order_tags']) || empty($order['tags'])) {
            return false;
        }

        $requiredTags = explode(',', strtolower($setting->other_details['order_tags']));
        $orderTags = array_map('trim', explode(',', strtolower($order['tags'])));
        
        return count(array_intersect($requiredTags, $orderTags)) === 0;
    }

    public function addShipmentTrackDetails($order_id, $data)
    {
        $response=[];
        DB::beginTransaction();
        try {
            // Delete existing tracking history for the order
            TrackingHistory::where('order_id', $order_id)->delete();
            $scans = $data['scans'] ?? [];
            $shipmentInfo = [];
            $origin = $data['origin'] ?? '';
            $destination = $data['destination'] ?? '';
            $current_status_date = $data['current_status_date'] ?? '';
            $current_status_code = $data['current_status_code'] ?? '';
            $pickup_date = $data['pickup_date'] ?? '';
            $expected_delivery_date = $data['expected_delivery_date'] ?? '';
            $pod = $data['pod'] ?? '';

            // Prepare shipment info data
            if ($origin) {
                $shipmentInfo['origin'] = $origin;
            }
            if ($destination) {
                $shipmentInfo['destination'] = $destination;
            }
            if ($current_status_code) {
                $shipmentInfo['current_status'] = $current_status_code;
            }
            if ($current_status_date) {
                $shipmentInfo['current_status_date'] = $current_status_date;
            }
            if ($pickup_date) {
                $shipmentInfo['pickedup_date'] = $pickup_date;
            }
            if ($expected_delivery_date) {
                $shipmentInfo['edd'] = $expected_delivery_date;
            }
            if ($pod) {
                $shipmentInfo['pod'] = $pod;
            }

            // Prepare tracking history data
            $insertHistory = [];
            if (!empty($scans)) {
                foreach ($scans as $scan) {
                    $insertHistory[] = [
                        'order_id' => $order_id,
                        'courier_id' => $data['courier_id'],
                        'tracking_number' => $data['tracking_number'],
                        'current_shipment_status' => $scan['status'],
                        'current_shipment_status_code' => $scan['current_status_code'],
                        'current_shipment_status_date' => $scan['date'],
                        'current_shipment_location' => $scan['location'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Insert tracking history in bulk
            if (!empty($insertHistory)) {
                TrackingHistory::insert($insertHistory); // Use insert for bulk inserts
            }

            // Update shipment info
            if (!empty($shipmentInfo)) {
                $shipment_info = DB::table('shipment_info')
                ->where('order_id', $order_id)->first();
                $shipmentInfo['updated_at'] = now();
                DB::table('shipment_info')
                    ->where('order_id', $order_id)
                    ->update($shipmentInfo);
               // $notificationdata = array();
               // $notificationdata['company_id'] = $shipment_info->company_id;
                //$notificationdata['current_status_code'] = $current_status_code;
               // $notificationdata['old_current_status_code'] = $shipment_info->current_status;
               // $notificationdata['order_id'] = $order_id;
                //Log(json_encode($notificationdata));
                //dispatch(new Shipmentmail($notificationdata));          
                
                //$notificationdata['event'] = $current_status_code;

                // $gateway = SmsGateway::where('status', 1)->where('company_id', $shipment_info->company_id)->first();
            
                // if($gateway){
                //     $order = Order::find($order_id);
                //     $notification = notification::create([
                //         'order_id' => $order->id,
                //         'company_id' => $shipment_info->company_id,
                //         'channel' => 'sms',
                //         'user_type' => 'buyer',
                //         'event' => $current_status_code,
                //         'customer_id' => $order->customer_id,
                //         'sent_status' => 0
                //     ]);
                //     $notificationdata['gateway_data'] = $gateway;
                //     $notificationdata['notification_id'] = $notification->id;
                //     dispatch(new SendSmsjob($notificationdata));
                // }

            }

            DB::commit();
            $response['success'] = 'Shipment has tracked successfully.';
           
        } catch (\Exception $e) {
            DB::rollBack();
            $response['error'] = $e->getMessage();
        }
        return $response;
    }

}
