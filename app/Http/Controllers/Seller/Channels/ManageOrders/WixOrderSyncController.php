<?php

namespace App\Http\Controllers\Seller\Channels\ManageOrders;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Models\ChannelSetting;
use App\Models\PaymentMapping;
use App\Models\Order;
use App\Models\BuyerInvoiceSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\ShipmentInfo;
use App\Services\OrderLogService;
use App\Models\OrderLog;
class WixOrderSyncController extends Controller
{
    
    public function syncOrders($companyId = null,$syncType="auto")
    {
        $companyId ??= session('company_id');

        $settings = ChannelSetting::where('company_id', $companyId)
            ->where('channel_code', 'wix')
            ->where('status', 1)
            ->get();

        $responseMessages = [];
        $successMessages = [];
        $errorMessages = [];

        if (!$settings->isEmpty()) {
            foreach ($settings as $setting) {
                $cursor = null;
                $alreadyImported = 0;
                $unmappedPaymentCount = 0;
                $updatedOrders = 0;
                $orderCount = 0;
                $allOrders = [];
                $minDate = now()->subDays(2)->startOfDay()->setTimezone('UTC')->format('Y-m-d\TH:i:s.v\Z');
                $baseUrl = 'https://www.wixapis.com/ecom/v1/orders/search';

                // You should ideally store and retrieve access token per store
                $other_details = ($setting->other_details)?json_decode($setting->other_details):[];
                $accessToken = $other_details->access_token ?? null;
                $expire_at = $other_details->token_expires_at ?? null;
                if($expire_at <= date('Y-m-d H:i:s')){
                    $setting = app(\App\Services\WixOAuthService::class)->createAccessToken($setting);
                    $other_details = ($setting->other_details)?json_decode($setting->other_details):[];
                    $accessToken = $other_details->access_token ?? null;
                }
                $fetch_status = $other_details->fetch_status??'NOT_FULFILLED';
                if (!$accessToken) {
                    $errorMessages[] = "{$setting->channel_title}: Access token missing.";
                    continue;
                }
                do {
                    
                    $payload = [
                        'search' => [
                            'filter' => [
                                'status' => [
                                    '$eq' => 'APPROVED'
                                ],
                                'fulfillmentStatus' => [
                                    '$in' => [$fetch_status]
                                ],
                                'createdDate' => [
                                    '$gte' => $minDate
                                ]
                            ],
                            'sort' => [
                                [
                                    'fieldName' => 'number',
                                    'order' => 'ASC'
                                ]
                            ],
                            'cursorPaging' => [
                                'limit' => 1,
                                'cursor' => $cursor
                            ]
                        ]
                    ];

                    $response = Http::withToken($accessToken)
                        ->withHeaders(['Content-Type' => 'application/json'])
                        ->post($baseUrl, $payload);

                    if (!$response->successful()) {                        
                        $errorMessages[] = "{$setting->channel_title}: Failed to fetch orders.".$response->json('message');
                        break;
                    }

                    $data = $response->json();
                    $orders = $data['orders'] ?? [];
                    $cursor = data_get($data, 'metadata.cursors.next');

                    // Sync fetched orders
                    $this->syncOrdersForSetting($orders, $setting, $orderCount, $alreadyImported,$updatedOrders,$unmappedPaymentCount, $companyId);
                    $allOrders = array_merge($allOrders, $orders);   

                } while (!empty($cursor));

                if ($orderCount > 0) {
                    $successMessages[] = "{$setting->channel_title}: {$orderCount} orders synced successfully.";
                } else {
                    $successMessages[] = "{$setting->channel_title}: No new orders found for sync.";
                }

                if ($updatedOrders > 0) {
                    $successMessages[] = "{$setting->channel_title}: {$updatedOrders} orders updated successfully.";
                }
            }
        }

        // Prepare flash messages
        if (!empty($errorMessages)) {
            $responseMessages['wix_error'] = implode('; ', $errorMessages);
        }

        if (!empty($successMessages)) {
            $responseMessages['wix_success'] = implode('; ', $successMessages);
        }

        return redirect()->route('order_list')->with($responseMessages);
    }

    private function syncOrdersForSetting($wixOrders, $setting, &$orderCount, &$alreadyImported,&$updatedOrders, &$unmappedPaymentCount, $companyId)
    {
        // Fetch payment mappings for this channel
        $paymentMappings = PaymentMapping::where('company_id', $companyId)
            ->where('channel_id', $setting->channel_id)
            ->get()
            ->keyBy('gateway_name');
            
        $buyer_invoice_settings = BuyerInvoiceSetting::where('company_id', $companyId)->first();
        $invoice_type = $buyer_invoice_settings->number_type??'order_number';
        $invoice_prefix = '';
        $invoice_start_from='';
        if($invoice_type=='custom_number'){
            $invoice_prefix = $buyer_invoice_settings->prefix??'';
            $invoice_start_from = $buyer_invoice_settings->start_from??'';
        }
        $orderService = new OrderService();
        foreach ($wixOrders as $orderData) {
            $vendor_order_id = $orderData['id'];            
            $orderData['channel_id'] =  $setting->channel_id;
            $orderData['company_id'] =  $companyId;            
            $orderData['paymentGateway'] = $orderData['paymentStatus'] ?? '';
            // if (!$orderData['paymentGateway']) {
            //     continue;
            // }
            $paymentCode = $orderService->getPaymentMethod($orderData, $paymentMappings, $unmappedPaymentCount);
            if (!$paymentCode) {
                continue; // Skip if unmapped payment
            }
            $orderFields = $this->mapOrderDetails($orderData, $setting->channel_id, $paymentCode);
            $orderFields['invoice_type'] = $invoice_type;
            $orderFields['invoice_prefix'] = $invoice_prefix;
            $orderFields['invoice_start_from'] = $invoice_start_from;
            $existorder = $orderService->isOrderAlreadyImported($orderFields['vendor_order_number'], $companyId, $setting->channel_id);
            if ($existorder) {
                $other_details = ($setting->other_details)?json_decode($setting->other_details,true):array();
                $pull_update_orders = $other_details['pull_update_orders']??0;
                if($existorder->status_code=='N' && $pull_update_orders){
                    $orderService->updateOrderAddress($existorder,$orderFields);
                    $updatedOrders++;
                    continue;
                }
                $alreadyImported++;
                continue;
            }
            $createdOrder = $orderService->createOrder($orderFields, $companyId);

            if (isset($createdOrder['id'])) {
                $orderCount++;
            }
        }
    }
    private function mapOrderDetails($order,$channelId, $paymentCode)
    {  
        $vendor_order_id = $order['id'];
        $payment_method = $order['paymentStatus']?? null;
        $create_at = (new \DateTime($order['createdDate']))->format('Y-m-d H:i:s');

        $billingInfo = $order['billingInfo']??array();
        $billingAddress = $billingInfo['address']??array();
        $billingContact = $billingInfo['contactDetails']??array();

        $shippingInfo = $order['shippingInfo']['logistics']??array();
        $shippingDestination = $shippingInfo['shippingDestination']??array();
        $shippingAddress = $shippingDestination['address']??array();
        $shippingContact = $shippingDestination['contactDetails']??array();
        
        //Phones
        $billing_phone = $billingContact['phone']??'';
        $shipping_phone = $shippingContact['phone']??'';
       
        //Names
        $billing_name = trim(($billingContact['firstName']??'').' '.($billingContact['lastName']??''));
        $shipping_name = trim(($shippingContact['firstName']??'').' '.($shippingContact['lastName']??''));

        //Address
        $billing_address = trim(($billingAddress['addressLine'] ?? '') . ' ' . ($billingAddress['addressLine2'] ?? ''));
        $shipping_address = trim(($shippingAddress['addressLine']??'').' '.($shippingAddress['addressLine2']??'')); 

        //Zipcodes
        $billing_zipcode = $billingAddress['postalCode']??'';
        $shipping_zipcode  = $shippingAddress['postalCode']??'';

        //cities
        $billing_city=  $billingAddress['city']??'';
        $shipping_city=  $shippingAddress['city']??'';

       
        //country codes
        $billing_country_code = $billingAddress['country']??'';
        $shipping_country_code = $shippingAddress['country']??'';

        // State codes
        $billing_state_code = isset($billingAddress['subdivision']) ? substr($billingAddress['subdivision'], -2) : '';
        $shipping_state_code = isset($shippingAddress['subdivision']) ? substr($shippingAddress['subdivision'], -2) : '';

        $final_name = $shipping_name ?: $billing_name;
        $final_phone = $shipping_phone ?: $billing_phone;
        $final_billing_address = $billing_address ?: $shipping_address;
        $final_shipping_address = $shipping_address ?: $billing_address;
        // Fill post fields
        $post_fields = [
            'vendor_order_id'     => $vendor_order_id,
            'vendor_order_number' => $order['number'],
            'channel_id'          => $channelId,
            'channel_order_date'  => $create_at,
            'fullname'            => $final_name,
            'email'               => $order['buyerInfo']['email']??'',
            'phone_number'        => $final_phone,
            'order_tags'          => '',
            'notes'               => '',
            'invoice_prefix'      => '',
            'invoice_number'      => '',
            'package_breadth'     => 10,
            'package_height'      => 10,
            'package_length'      => 10,
            'package_dead_weight' => 0.05,
            'currency_code'       => $order['currency'] ?? '',
            'payment_mode'        => $paymentCode,
            'payment_method'      => $payment_method,
            'financial_status'    => strtolower($order['paymentStatus']),
            // Billing Address
            'b_fullname'       => $billing_name ?: $shipping_name,
            'b_company'        => $billingContact['company'] ?? '',
            'b_complete_address' => $final_billing_address,
            'b_zipcode'        => $billingAddress['postalCode'] ?? $shippingAddress['postalCode'] ?? null,
            'b_phone'          => $billing_phone ?: $shipping_phone,
            'b_city'           => $billingAddress['city'] ?? $shippingAddress['city'] ?? '',
            'b_landmark'       => null,
            'b_state_code'     => $billing_state_code ?: $shipping_state_code,
            'b_country_code'   => $billingAddress['country'] ?? $shippingAddress['country'] ?? '',

            // Shipping Address
            's_fullname'       => $shipping_name ?: $billing_name,
            's_company'        => $shippingContact['company'] ?? '',
            's_complete_address' => $final_shipping_address,
            's_zipcode'        => $shippingAddress['postalCode'] ?? $billingAddress['postalCode'] ?? null,
            's_phone'          => $shipping_phone ?: $billing_phone,
            's_city'           => $shippingAddress['city'] ?? $billingAddress['city'] ?? '',
            's_landmark'       => null,
            's_state_code'     => $shipping_state_code ?: $billing_state_code,
            's_country_code'   => $shippingAddress['country'] ?? $billingAddress['country'] ?? '',
        ];         
    
        $post_fields['financial_status'] = strtolower($order['paymentStatus']);     
        //Order products
        $lineItems =  $order['lineItems']??[];
    
        foreach($lineItems as $item){
            $order_products = array();
            $taxLines = $item['taxDetails']??[];
            $tax_rate = (isset($taxLines['taxRate']))?$taxLines['taxRate']:0;
            $tax_amount = (isset($taxLines['totalTax']['amount']))?$taxLines['totalTax']['amount']:0;
            $order_products['product_name'] = $item['productName']['original']??'';
            $order_products['sku'] = $item['physicalProperties']['sku']?:"";
            $order_products['line_item_id'] = null;
            $order_products['unit_price'] = $item['lineItemPrice']['amount']??0;
            $order_products['quantity'] = $item['quantity'];
            $order_products['discount'] = $item['totalDiscount']['amount']??0;
            $order_products['shipping'] = null;
            $order_products['hsn'] = null;
            $order_products['tax_rate'] = $tax_rate;
            $order_products['tax_name'] = null;
            $order_products['tax_type'] = 0;
            $order_products['tax_amount'] = $tax_amount;
            $order_products['total_price'] = $order_products['unit_price']*$order_products['quantity'];
            $post_fields['order_products'][]=$order_products;
        }
        $order_pricees = $order['priceSummary'];
        $totalWithGiftCard=0;
        $totalWithoutGiftCard=0;
        $order_totals = [];
      
        if(isset($order_pricees['subtotal'])){
            $subtotal = $order_pricees['subtotal']['amount'];
            $post_fields['sub_total'] = $subtotal;
            if($subtotal > 0){
                $order_totals[] =  array(
                    "title" =>"Subtotal",
                    "code" => "sub_total",
                    "value" =>$subtotal,
                    "sort_order" => 1
                );
            }
        }
        if(isset($order_pricees['shipping'])){
            $shipping_cost = $order_pricees['shipping']['amount'];
            if($shipping_cost > 0){
                $order_totals[] =  array(
                    "title" => "Shipping",
                    "code" => "shipping",
                    "value" => $shipping_cost,
                    "sort_order" => 2
                );
            }
        }
        if(isset($order_pricees['tax'])){
            $totalTax = $order_pricees['tax']['amount'];
            if($totalTax > 0){
                $order_totals[] =  array(
                    "title" => "Tax",
                    "code" => "tax",
                    "value" => $totalTax,
                    "sort_order" => 3
                );
            }
        }
        if(isset($order_pricees['discount'])){
            $totalDiscounts = $order_pricees['discount']['amount'];
            if($totalDiscounts > 0){
               $order_totals[] =  array(
                    "title" => "Discount",
                    "code" => "discount",
                    "value" => $totalDiscounts,
                    "sort_order" => 4
                );
            }
        }
        if(isset($order_pricees['total'])){
            $order_total = $order_pricees['total']['amount'];
            $post_fields['order_total']  = $order_total;
            if($order_total > 0){
                $order_totals[] =   array(
                "title" => "Total",
                "code" => "total",
                "value" => $order_total,
                "sort_order" => 9
                );
            }
        }
        if(isset($order_pricees['totalWithGiftCard'])){
            $totalWithGiftCard = $order_pricees['totalWithGiftCard']['amount'];              
        }
        if(isset($order_pricees['totalWithoutGiftCard'])){
            $totalWithoutGiftCard = $order_pricees['totalWithoutGiftCard']['amount'];
            
        }
        if(isset($order_pricees['totalAdditionalFees'])){
            $totalAdditionalFees = $order_pricees['totalAdditionalFees']['amount'];
            if($totalAdditionalFees > 0){
                $order_totals[] =  array(
                    "title" => "Other Charges",
                    "code" => "other",
                    "value" => $totalAdditionalFees,
                    "sort_order" => 7
                );
            }
        }
        
        $giftwrap = $totalWithGiftCard-$totalWithoutGiftCard;
        if($giftwrap > 0){
            $order_totals[] =  array(
                "title" => "Giftwrap",
                "code" => "giftwrap",
                "value" => $giftwrap,
                "sort_order" => 5
            );
        }       
        $post_fields['order_totals'] = $order_totals; 
        return $post_fields;
    
    }
   

}
