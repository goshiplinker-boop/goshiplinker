<?php

namespace App\Http\Controllers\Seller\Channels\ManageOrders;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Services\ShopifyGraphQLService;
use App\Models\ChannelSetting;
use App\Jobs\ShopifyOrderSyncJob;
use App\Models\PaymentMapping;
use App\Models\Order;
use App\Models\BuyerInvoiceSetting;
use Illuminate\Support\Facades\Log;
use App\Jobs\FulfillShopifyOrderJob;
use App\Models\ShipmentInfo;
use App\Services\OrderLogService;
use App\Models\OrderLog;
use App\Models\OrderProduct;
use App\Models\CourierMapping;
use App\Models\PickupLocation;
class ShopifyOrderSyncController extends Controller
{
    // public function syncOrders($companyId = 2, $channel_id = 3)
    // {    
    //     $companyId = $companyId ?: auth()->guard('web')->user()->company_id;
    //     $settings = ChannelSetting::where('company_id', $companyId)
    //     ->where('channel_id', $channel_id)
    //     ->where('channel_code', 'shopify')->first();
    //     ShopifyOrderSyncJob::dispatch($settings->secret_key,$settings->channel_url,$channel_id,$companyId);
    //     return response()->json(['message' => 'Order sync initiated.']);
    // }
    public function syncOrders($companyId=null,$syncType="auto")
    {    
        $companyId ??= session('company_id');
        $settings = ChannelSetting::where('company_id', $companyId)
            ->where('channel_code', 'shopify')
            ->where('status', 1)
            ->get();
        
        $location_id = PickupLocation::where('company_id', $companyId)
            ->where('default', 1)
            ->where('status', 1)
            ->value('id');
        $responseMessages = [];
        $success =[];
        $errors =[];
        if($settings){
            foreach ($settings as $setting) {
                // Initialize Shopify GraphQL service and fetch paginated orders
                $shopifyGraphQLService = new ShopifyGraphQLService();
                $ordersAfter = null;
                $alreadyImported = 0;
                $unmappedPaymentCount = 0;
                $unmappedCourierCount = 0;
                $updatedOrders =0;
                $orderCount = 0;
                $allOrders = [];
                $days = now()->subDays(2)->toDateString();
                $last_order_id = 0;
                if($syncType=='auto'){
                    $latest = Order::where('company_id', $companyId)
                            ->where('channel_id', $setting->channel_id)
                            ->orderBy('channel_order_date', 'desc')
                            ->first();
                    $last_order_id = $latest->vendor_order_id??'';
                }
                
                $other_details  = ($setting->other_details)?json_decode($setting->other_details,true):array();
                $fetch_status = $other_details['fetch_status']??'unfulfilled';
                if($fetch_status=='fulfilled'){
                    $order_fetch_status = 'fulfillment_status:fulfilled';
                }elseif($fetch_status=='both'){
                    $order_fetch_status = "((status:open AND fulfillment_status:unfulfilled) OR fulfillment_status:fulfilled)";
                }else{
                    $order_fetch_status = "status:open AND fulfillment_status:unfulfilled";
                }
                if($fetch_status !='unfulfilled' && empty($location_id)){
                    $errors[] = $setting->channel_title . ' — Please create a pickup location before syncing fulfilled orders. <a href="' . route('pickup_locations.index') . '" target="_blank">Click here</a>';
                    break;
                }               
                $searchQuery = $order_fetch_status." AND created_at:>=$days";
                if($syncType=='auto' && $last_order_id){
                    $searchQuery .=" AND id:>=$last_order_id";
                }
                do {
                    $response = $shopifyGraphQLService->query(
                        $setting->channel_url, 
                        $setting->secret_key, 
                        $shopifyGraphQLService->buildOrderQuery(), 
                        [
                            'ordersAfter' => $ordersAfter,
                            'searchQuery' => $searchQuery
                        ]
                    );
                    if ($response->failed()) {
                        $errors[] = "Failed to fetch orders from Shopify: " . $response->body();
                        
                        if(isset($response['errors'])){
                            $errors[] = $response['errors'];
                        }
                        break;
                    }
                    $orders = $response->json('data.orders.edges');
                    $ordersAfter = $response->json('data.orders.pageInfo.endCursor');
                    $hasNextPage = $response->json('data.orders.pageInfo.hasNextPage');
                    $this->syncOrdersForSetting($orders, $setting, $orderCount, $alreadyImported,$updatedOrders,$unmappedPaymentCount, $companyId,$unmappedCourierCount,$location_id);
                    $allOrders = array_merge($allOrders, $orders);
                } while ($hasNextPage);  
                $success[] = $orderCount > 0 ? $setting->channel_title ." ".$orderCount." Orders synced successfully." : "No new orders found for $setting->channel_title sync.";
                if($updatedOrders>0){
                    $success[] = $setting->channel_title ." ".$updatedOrders." Orders updated successfully.";

                }
            }
            
        }        
        if($errors){
            $responseMessages['shopify_error'] =implode('; ', $errors);
        }
        if($success){
            $responseMessages['shopify_success'] =implode('; ', $success);
        }
        return redirect()->route('order_list')->with($responseMessages);
    }
    private function syncOrdersForSetting($shopifyOrders, $setting, &$orderCount, &$alreadyImported,&$updatedOrders, &$unmappedPaymentCount, $companyId,&$unmappedCourierCount,$location_id)
    {
        $other_details = ($setting->other_details)?json_decode($setting->other_details,true):array();
        $order_fetch_status = $other_details['fetch_status']??'';
        // Fetch payment mappings for this channel
        $paymentMappings = PaymentMapping::where('company_id', $companyId)
            ->where('channel_id', $setting->channel_id)
            ->get()
            ->keyBy('gateway_name');
        $courierMappings=[];
        if($order_fetch_status !='unfulfilled'){
            // Fetch courier mappings for this channel
            $courierMappings = CourierMapping::where('company_id', $companyId)
                ->where('channel_id', $setting->channel_id)
                ->get()
                ->keyBy('courier_name');           

        }
        
        $buyer_invoice_settings = BuyerInvoiceSetting::where('company_id', $companyId)->first();
        $invoice_type = $buyer_invoice_settings->number_type??'order_number';
        $invoice_prefix = '';
        $invoice_start_from='';
        if($invoice_type=='custom_number'){
            $invoice_prefix = $buyer_invoice_settings->prefix??'';
            $invoice_start_from = $buyer_invoice_settings->start_from??'';
        }
        $orderService = new OrderService();
        foreach ($shopifyOrders as $orderData) {
            $orderNode = $orderData['node'];
            $vendor_order_id = basename($orderNode['id']);      
            $displayFinancialStatus = $orderNode['displayFinancialStatus']??'';          
            $orderNode['channel_id'] =  $setting->channel_id;
            $orderNode['company_id'] =  $companyId;            
            $orderNode['paymentGateway'] = $orderNode['paymentGatewayNames'][0] ?? '';
            // if (!$orderNode['paymentGateway']) {
            //     continue;
            // }
            $orderNode['paymentGateway'] = trim($orderNode['paymentGateway']);
            if($displayFinancialStatus=='PARTIALLY_PAID'){
                $orderNode['paymentGateway'] = $orderNode['paymentGateway']."(partialy paid)";
            }
            $paymentCode = $orderService->getPaymentMethod($orderNode, $paymentMappings, $unmappedPaymentCount);
            if (!$paymentCode) {
                continue; // Skip if unmapped payment
            }
            //code for fulfilment order syncs
            $displayFulfillmentStatus = $orderNode['displayFulfillmentStatus']??'';            
            if($order_fetch_status !='unfulfilled' && $displayFulfillmentStatus=='FULFILLED'){
                if(empty($location_id)){
                    continue;
                }
                $fulfillments =  $orderNode['fulfillments']??[];
                $fulfillments = $fulfillments['0']??[];
                $orderNode['fulfillment_details']=[];
                if($displayFulfillmentStatus=='FULFILLED' && !empty($fulfillments)){
                    $trackingInfo = $fulfillments['trackingInfo']??[];
                    $trackingInfo = $trackingInfo['0']??[];
                    $orderNode['fulfillment_details'] = [
                        "fulfillment_id"=>$fulfillments['id'],
                        "pickedup_location_id"=>$location_id,
                        "courier_name" => $trackingInfo['company']??'',
                        "tracking_number" => $trackingInfo['number']??''
                    ];
                }
                if(empty($orderNode['fulfillment_details'])){
                    continue;
                }
                $courier_id = $orderService->getCourierId($orderNode, $courierMappings, $unmappedCourierCount);
                if (!$courier_id) {
                    continue; // Skip if unmapped courier
                }
                $orderNode['fulfillment_details']['courier_id'] = $courier_id;
            }
            $orderFields = $this->mapOrderDetails($orderNode, $setting->channel_id, $paymentCode);
            $orderFields['invoice_type'] = $invoice_type;
            $orderFields['invoice_prefix'] = $invoice_prefix;
            $orderFields['invoice_start_from'] = $invoice_start_from;
            $existorder = $orderService->isOrderAlreadyImported($orderFields['vendor_order_number'], $companyId, $setting->channel_id);
            
            if ($existorder) {                
                $pull_update_orders = $other_details['pull_update_orders']??0;
                if($existorder->status_code=='N' && $pull_update_orders){
                    $orderService->updateOrderAddress($existorder,$orderFields);
                    $updatedOrders++;
                    continue;
                }
                $alreadyImported++;
                continue;
            }
            if ($orderService->shouldSkipOrderBasedOnTags($orderNode, $setting)) {
                continue;
            }
            $fulfillment_details = $orderFields['fulfillment_details']??[];
            if($order_fetch_status !='unfulfilled' && $displayFulfillmentStatus=='FULFILLED'){
                if(empty($fulfillment_details)){
                    continue; 
                }        

            }
            $createdOrder = $orderService->createOrder($orderFields, $companyId);

            if (isset($createdOrder['id'])) {
                $orderCount++;
            }
        }
    }

   

    private function mapOrderDetails($order,$channelId, $paymentCode)
    {  
        $vendor_order_id = basename($order['id']);
        $payment_method = $order['paymentGatewayNames'][0] ?? null;
        $create_at = (new \DateTime($order['createdAt']))->format('Y-m-d H:i:s');
        $billingAddress = (!empty($order['billingAddress']))?$order['billingAddress']:array();
        $shippingAddress = (!empty($order['shippingAddress']))?$order['shippingAddress']:array();
        $billingAddressMatchesShippingAddress = $order['billingAddressMatchesShippingAddress'];      
        $baddress1 = $billingAddress['address1']??'';  
        $baddress2 = $billingAddress['address2']??'';  
        $saddress1 = $shippingAddress['address1']??'';  
        $saddress2 = $shippingAddress['address2']??'';   
        $b_complete_address = $baddress1.' '.$baddress2;
        $s_complete_address = $saddress1.' '.$saddress2;
        $customer = $order['customer'];
        $tags = $order['tags']??'';
        $tags = is_array($tags)?implode(',',$tags):$tags;
        $post_fields=array();
        $post_fields[ 'vendor_order_id'] =$vendor_order_id;
        $post_fields['vendor_order_number'] =  ltrim($order['name'],'#');
        $post_fields['channel_id'] = $channelId;
        $post_fields['channel_order_date'] = $create_at;
        $post_fields['fullname']  = $customer['displayName']??'';
        $post_fields['email'] = $order['email'];
        $post_fields['phone_number'] = $order['phone']??"43234444444";           
        $post_fields['order_tags'] = $tags;
        $post_fields['notes'] = $order['note'];
        $post_fields['invoice_prefix'] = '';
        $post_fields['invoice_number'] = '';
        $post_fields['package_breadth']  = 10;
        $post_fields['package_height']  = 10;
        $post_fields['package_length']  = 10;
        $post_fields['package_dead_weight'] = ($order['totalWeight']>0)?$order['totalWeight']/1000:0.05;
        $post_fields['currency_code'] = $order['currencyCode'];
        $post_fields['payment_mode']  = $paymentCode;
        $post_fields['payment_method']  = $payment_method;        
        $post_fields['order_total']  = $order['totalPrice'];
        //Billing address
        $post_fields['b_fullname'] = $billingAddress['name']??'';
        $post_fields['b_company'] = $billingAddress['company']??'';
        $post_fields['b_complete_address']  = trim($b_complete_address);           
        $post_fields['b_zipcode'] = $billingAddress['zip']??'';
        $post_fields['b_phone'] = $billingAddress['phone']??'';
        $post_fields['b_city'] = $billingAddress['city']??'';
        $post_fields['b_landmark'] = null;
        $post_fields['b_state_code'] = $billingAddress['provinceCode']??'';
        $post_fields['b_country_code'] = $billingAddress['countryCode']??'';
        //Shipping address
        $post_fields['s_fullname'] = $shippingAddress['name']??'';
        $post_fields['s_company'] = $shippingAddress['company']??'';
        $post_fields['s_complete_address']  = trim($s_complete_address);           
        $post_fields['s_zipcode'] = $shippingAddress['zip']??'';
        $post_fields['s_phone'] = $shippingAddress['phone']??'';
        $post_fields['s_city'] = $shippingAddress['city']??'';
        $post_fields['s_landmark'] = null;
        $post_fields['s_state_code'] = $shippingAddress['provinceCode']??'';
        $post_fields['s_country_code'] = $shippingAddress['countryCode']??'';             
        $post_fields['customer_ip_address'] = $order['clientIp']??'';
        $post_fields['financial_status'] = strtolower($order['displayFinancialStatus']);     
        //Order products
        $lineItems =  $order['lineItems']['edges']??[];
        $tax_type = $order['taxesIncluded']===true?0:1;
        $tax_exempt = $order['taxExempt']===true?1:0;
        $subtotal = 0;
        foreach($lineItems as $lineItem){
            $order_products = array();
            $item = $lineItem['node'];
            if($tax_exempt==0){
                $taxLines = $item['taxLines'][0]??[];
                $tax_rate = (isset($taxLines['rate']))?$taxLines['rate']:0;
                $tax_amount = (isset($taxLines['price']))?$taxLines['price']:0;
                $tax_name = (isset($taxLines['title']))?$taxLines['title']:null;
            }else{
                $tax_rate=0;
                $tax_amount=0;
                $tax_name = null;
            }
            $discountAllocations = $item['discountAllocations'][0]??[];
            $allocatedAmountSet = $discountAllocations['allocatedAmountSet']['shopMoney']??[];
            $product_discount = $allocatedAmountSet['amount']??0;
            $line_item_id = basename($item['id']);
            $order_products['product_name'] = $item['name'];
            $order_products['sku'] = $item['sku']?:"";
            $order_products['line_item_id'] = $line_item_id;
            $order_products['unit_price'] = $item['originalUnitPrice'];
            $order_products['quantity'] = $item['quantity'];
            $order_products['discount'] = $product_discount;
            $order_products['shipping'] = null;
            $order_products['hsn'] = null;
            $order_products['tax_rate'] = $tax_rate;
            $order_products['tax_type'] = $tax_type;
            $order_products['tax_name'] = $tax_name;
            $order_products['tax_amount'] = $tax_amount;
            $order_products['total_price'] = $item['originalTotal'];
            $post_fields['order_products'][]=$order_products;
            $subtotal += $item['originalTotal'];
        }
        $post_fields['sub_total']  = $subtotal;
        $displayFinancialStatus = $order['displayFinancialStatus']??'';   
        $totalReceived = ($displayFinancialStatus=='PARTIALLY_PAID')?$order['totalReceived']:0;
        $order_totals = array(
            array(
              "title" =>"Subtotal",
              "code" => "sub_total",
              "value" =>$subtotal,
              "sort_order" => 1
            ),                                         
            array(
              "title" => "Total",
              "code" => "total",
              "value" => $order['totalPrice']-$totalReceived,
              "sort_order" => 9
            )
        );
        if($order['totalShippingPrice']>0){
            $order_totals[] =  array(
                "title" => "Shipping",
                "code" => "shipping",
                "value" => $order['totalShippingPrice'],
                "sort_order" => 2
            );
        }
        if($order['totalTax']>0){
            $order_totals[] =  array(
                "title" => "Tax",
                "code" => "tax",
                "value" => $order['totalTax'],
                "sort_order" => 3
            );
        }
        if($order['totalDiscounts']>0){
            $order_totals[] =  array(
                "title" => "Discount",
                "code" => "discount",
                "value" => $order['totalDiscounts'],
                "sort_order" => 4
            );
        }
        if($totalReceived>0){
            $post_fields['order_total'] = $post_fields['order_total']-$totalReceived;
            $order_totals[] =  array(
                "title" => "Advanced Paid",
                "code" => "advanced",
                "value" => $totalReceived,
                "sort_order" => 8
            );
        }
        $post_fields['order_totals'] = $order_totals;
        $post_fields['fulfillment_details'] = $order['fulfillment_details']??[];
        
        return $post_fields;
    
    }
    public function fulfillOrder($channel_settings,$trackingInfo)
    {   
        
        $ShopifyGraphQLService = new ShopifyGraphQLService();
        $shopifyAccessToken =$channel_settings->secret_key; 
        $shopUrl =$channel_settings->channel_url;
        $other_details =$channel_settings->other_details;
        $other_details = ($other_details)?json_decode($other_details,true):array();
        $notify_customer = $other_details['notify_customer']??false;
        $vendor_order_id = $trackingInfo['vendor_order_id'];
        $trackingLinks = json_decode(env('TRACKING_LINKS', '{}'), true);
        $tracking_url = $trackingLinks[$trackingInfo['courier_code']] ?? null;
        $tracking_company = ucfirst($trackingInfo['courier_code']);
        $order_id = $trackingInfo['order_id'];
        $website_domain = $trackingInfo['website_domain'] ?? '';
        if (!empty($website_domain)) {
            $tracking_url = rtrim(env('APP_URL'), '/') . '/' . trim($website_domain, '/') . '/track/'.$trackingInfo['tracking_number'];
        }
        $trackingInfo = [
            'company_id' => $trackingInfo['company_id'],
            'order_id' => $trackingInfo['order_id'],
            'vendor_order_id' => $trackingInfo['vendor_order_id'],
            'number' => $trackingInfo['tracking_number'],            
            'url' => $tracking_url,
            'company' => $tracking_company,
            'notify_customer' => $notify_customer,
        ];
        ShipmentInfo::where('order_id',$order_id)->update(['fulfillment_status'=>2]);
        // Dispatch the job
        try {
            $fulfillOrder = $ShopifyGraphQLService->fetchFulfillmentOrderId($shopUrl,$shopifyAccessToken,$vendor_order_id);  
            $updatestatus=3;  
            if (isset($fulfillOrder['error'])) {
                if($fulfillOrder['error']=='already fulfilled'){
                    $updatestatus = 1;
                }                
                $orderLog = new OrderLogService();
                $orderLog->createLog(
                    $trackingInfo['order_id'],
                    $trackingInfo['company_id'],
                    $trackingInfo['vendor_order_id'],
                    'fulfillment',
                    json_encode($fulfillOrder['request']),
                    $fulfillOrder['response'],
                    false);
            }else{
                $order_product = OrderProduct::where('order_id', $trackingInfo['order_id'])
                    ->pluck('quantity', 'line_item_id')
                    ->toArray();
                $trackingInfo['line_items'] = $order_product;
                // Step 2: Fulfill the Order
                $fulfillmentId = $ShopifyGraphQLService->fulfillOrder($fulfillOrder,$trackingInfo,$shopUrl,$shopifyAccessToken);
                if(!empty($fulfillmentId)){
                    $updatestatus=1;  
                    $fulfillmentId = basename($fulfillmentId);
                    OrderProduct::where('order_id', $trackingInfo['order_id'])->update(['fulfillment_id' => $fulfillmentId]);
                }                
            } 
            ShipmentInfo::where('order_id',$trackingInfo['order_id'])
                    ->update(['fulfillment_status'=>$updatestatus]);

        } catch (\Exception $e) {
            ShipmentInfo::where('order_id',$trackingInfo['order_id'])
                ->update(['fulfillment_status'=>3]);
                $orderLog = new OrderLogService();
                $orderLog->createLog(
                $trackingInfo['order_id'],
                $trackingInfo['company_id'],
                $trackingInfo['vendor_order_id'],
                'fulfillment',
                null,
                json_encode([$e->getMessage()]),
                false
            );
        }
        //$orderLog = new OrderLogService();
        //FulfillShopifyOrderJob::dispatch($shopifyAccessToken, $shopUrl, $vendor_order_id, $trackingInfo, $orderLog);

        return response()->json(['message' => 'Fulfillment process started.']);
    }

}
