<?php

namespace App\Http\Controllers\Seller\Channels\ManageOrders;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Models\ChannelSetting;
use App\Models\PaymentMapping;
use App\Models\Order;
use App\Models\BuyerInvoiceSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\ShipmentInfo;
use App\Services\OrderLogService;
class ShopbaseOrderSyncController extends Controller
{
   
    public function syncOrders($companyId=null,$syncType="auto")
    {    
        $companyId ??= session('company_id');
        $settings = ChannelSetting::where('company_id', $companyId)
            ->where('channel_code', 'shopbase')
            ->where('status', 1)
            ->get();
        
       
        $responseMessages = [];
        $success =[];
        $errors =[];
        if($settings){
            foreach ($settings as $setting) {
                try {
                    $alreadyImported = 0;
                    $updatedOrders=0;
                    $unmappedPaymentCount = 0;
                    $orderCount = 0;
                    $shop = $setting->channel_url;
                    $consumer_key = $setting->client_id;
                    $consumer_secret = $setting->secret_key;
                    $page = 1;
                    $perPage = 100;
                    $allOrders = [];
                    $last_order_id = 0;
                    if($syncType=='auto'){
                        $latest = Order::where('company_id', $companyId)
                                ->where('channel_id', $setting->channel_id)
                                ->orderBy('created_at', 'desc')
                                ->first();
                        $last_order_id = $latest->vendor_order_id??'';
                    }
                    $queryurl = '';
                     if($syncType=='auto' && $last_order_id){
                        $queryurl = '&since_id='.$last_order_id;
                    }
                    $min_date = now()->subDays(2)->toDateString();
                    $counturl = "https://{$consumer_key}:{$consumer_secret}@{$shop}/admin/orders/count.json?status=open&fulfillment_status=unshipped&created_at_min=".urlencode($min_date).$queryurl;
                    $response = Http::timeout(120)->withHeaders([
                        'Accept' => 'application/json',
                    ])->get($counturl);
                    $allOrders = [];
                    if ($response->successful()) {
                        $countorder = $response->json()['count']??0;
                        
                        if($countorder>0){
                            $page = ceil($countorder / $perPage);
                            do {
                                try {
                                    $url = "https://{$consumer_key}:{$consumer_secret}@{$shop}/admin/orders.json?status=open&fulfillment_status=unshipped&limit={$perPage}&page={$page}&created_at_min=".urlencode($min_date).$queryurl;
                                
                                    $response = Http::timeout(120)
                                        ->retry(3, 1000) // 3 attempts, 1 second delay
                                        ->withHeaders([
                                            'Accept' => 'application/json',
                                        ])
                                        ->get($url);
                                    if (!$response->successful()) {
                                        $errors[] = $response->json()['errors'] ?? 'Unknown API Error';
                                        break;
                                    }                    
                                    $apiResult = $response->json();
                                    $orders = $apiResult['orders'] ?? [];
                                    if(empty($orders)){
                                        break;
                                    }
                                    usort($orders, function ($a, $b) {
                                        return $a['id'] <=> $b['id']; // Ascending order
                                    });
                                    $this->syncOrdersForSetting($orders, $setting, $orderCount, $alreadyImported,$updatedOrders,$unmappedPaymentCount, $companyId);
                                    $allOrders = array_merge($allOrders, $orders);                            
                                    $page--;
                                    
                                } catch (Exception $e) {
                                    $errors[] = "Error fetching orders from Shopbase: " . $e->getMessage();
                                    break;
                                }
                            } while ($page>0);
                            $success[] =  $orderCount > 0 ? $setting->channel_title ." ".$orderCount." Orders synced successfully." : "No new orders found for $setting->channel_title sync.";
                            if($updatedOrders>0){
                                $success[] = $setting->channel_title ." ".$updatedOrders." Orders updated successfully.";

                            }
                        }
                    }  
                } catch (Exception $e) {
                    $errors[] = "Error processing setting ID {$setting->channel_id}: " . $e->getMessage();
                }
            }
            
        }        
        if($errors){
            $responseMessages['shopbase_error'] =implode('; ', $errors);
        }
        if($success){
            $responseMessages['shopbase_success'] =implode('; ', $success);
        }
        return redirect()->route('order_list')->with($responseMessages);
    }   
    private function syncOrdersForSetting($shopbaseOrders, $setting, &$orderCount, &$alreadyImported,&$updatedOrders, &$unmappedPaymentCount, $companyId)
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
        foreach ($shopbaseOrders as $order) {
            $vendor_order_id = $order['id'];
            $order['channel_id'] =  $setting->channel_id;
            $order['company_id'] =  $companyId;
            $paymentGateway = $order['payment_gateway_names'][0] ?? '';
            // if (!$paymentGateway) {
            //     continue;
            // }
            $order['paymentGateway'] = $paymentGateway;
            $paymentCode = $orderService->getPaymentMethod($order, $paymentMappings, $unmappedPaymentCount);
            if (!$paymentCode) {
                continue; // Skip if unmapped payment
            }
            $orderFields = $this->mapOrderDetails($order, $setting->channel_id, $paymentCode);
            $orderFields['invoice_type'] = $invoice_type;
            $orderFields['invoice_prefix'] = $invoice_prefix;
            $orderFields['invoice_start_from'] = $invoice_start_from;
            $existorder = $orderService->isOrderAlreadyImported($orderFields['vendor_order_number'], $companyId, $setting->channel_id);
            if ($existorder) {
                // $other_details = ($setting->other_details)?json_decode($setting->other_details,true):array();
                // $pull_update_orders = $other_details['pull_update_orders']??0;
                // if($existorder->status_code=='N' && $pull_update_orders){
                //     $orderService->updateOrderAddress($existorder,$orderFields);
                //     $updatedOrders++;
                // }
                $alreadyImported++;
                continue;
            }            

            if ($orderService->shouldSkipOrderBasedOnTags($order, $setting)) {
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
        $payment_method = $order['payment_gateway_names'][0] ?? null;
        $create_at = (new \DateTime($order['created_at']))->format('Y-m-d H:i:s');
        $billingAddress = $order['billing_address']?:[];
        $shippingAddress = $order['shipping_address']?:[];
        $baddress1 = $billingAddress['address1']??'';  
        $baddress2 = $billingAddress['address2']??'';  
        $saddress1 = $shippingAddress['address1']??'';  
        $saddress2 = $shippingAddress['address2']??'';   
        $b_complete_address = $baddress1.' '.$baddress2;
        $s_complete_address = $saddress1.' '.$saddress2;
        $billingAddressMatchesShippingAddress = $order['billing_shipping_same_address'];  
             
        $customer = $order['customer'];
        $customer_first_name = $customer['first_name']??'';
        $customer_last_name = $customer['last_name']??'';
        $tags = $order['tags']??'';
        $tags = is_array($tags)?implode(',',$tags):$tags;
        $post_fields=array();
        $post_fields[ 'vendor_order_id'] =$vendor_order_id;
        $post_fields['vendor_order_number'] =  ltrim($order['name'],'#');
        $post_fields['channel_id'] = $channelId;
        $post_fields['channel_order_date'] = $create_at;
        $post_fields['fullname']  = trim($customer_first_name.' '.$customer_last_name);
        $post_fields['email'] = $order['email'];
        $post_fields['phone_number'] = $order['phone']??"43234444444";           
        $post_fields['order_tags'] = $tags;
        $post_fields['notes'] = $order['note'];
        $post_fields['invoice_prefix'] = '';
        $post_fields['invoice_number'] = '';
        $post_fields['package_breadth']  = 10;
        $post_fields['package_height']  = 10;
        $post_fields['package_length']  = 10;
        $post_fields['package_dead_weight'] = ($order['total_weight']>0)?$order['total_weight']:0.05;
        $post_fields['currency_code'] = $order['currency'];
        $post_fields['payment_mode']  = $paymentCode;
        $post_fields['payment_method']  = $payment_method;
        $post_fields['sub_total']  = $order['subtotal_price'];
        $post_fields['order_total']  = $order['total_price'];
        //Billing address
        $post_fields['b_fullname'] = $billingAddress['name']??'';
        $post_fields['b_company'] = $billingAddress['company']??'';
        $post_fields['b_complete_address']  = trim($b_complete_address);           
        $post_fields['b_zipcode'] = $billingAddress['zip']??'';
        $post_fields['b_phone'] = $billingAddress['phone']??'';
        $post_fields['b_city'] = $billingAddress['city']??'';
        $post_fields['b_landmark'] = null;
        $post_fields['b_state_code'] = $billingAddress['province_code']??'';
        $post_fields['b_country_code'] = $billingAddress['country_code']??'';
        //Shipping address
        $post_fields['s_fullname'] = $shippingAddress['name']??'';
        $post_fields['s_company'] = $shippingAddress['company']??'';
        $post_fields['s_complete_address']  = trim($s_complete_address);           
        $post_fields['s_zipcode'] = $shippingAddress['zip']??'';
        $post_fields['s_phone'] = $shippingAddress['phone']??'';
        $post_fields['s_city'] = $shippingAddress['city']??'';
        $post_fields['s_landmark'] = null;
        $post_fields['s_state_code'] = $shippingAddress['province_code']??'';
        $post_fields['s_country_code'] = $shippingAddress['country_code']??'';             
        $post_fields['customer_ip_address'] = $order['additional_information']['hostname']??$order['client_ip'];
        $post_fields['financial_status'] = strtolower($order['financial_status']);     
        //Order products
        $lineItems =  $order['line_items']??[];
    
        foreach($lineItems as $item){
            $order_products = array();
            $line_item_id = basename($item['id']);
            $order_products['product_name'] = $item['name'];
            $order_products['sku'] = $item['sku']?:"";
            $order_products['line_item_id'] = $line_item_id;
            $order_products['unit_price'] = $item['price'];
            $order_products['quantity'] = $item['quantity'];
            $order_products['discount'] = $item['total_discount'];
            $order_products['shipping'] = null;
            $order_products['hsn'] = null;
            $order_products['tax_rate'] = $item['tax_rate'];
            $order_products['tax_name'] = null;
            $order_products['tax_type'] = 0;
            $order_products['tax_amount'] = $item['tax_amount'];
            $order_products['total_price'] = $item['price']*$item['quantity'];
            $post_fields['order_products'][]=$order_products;
        }
        $total_shipping = $order['shipping_lines']['0']['price']??0;
        $order_totals = array(
            array(
              "title" =>"Subtotal",
              "code" => "sub_total",
              "value" =>$order['subtotal_price'],
              "sort_order" => 1
            ),                                         
            array(
              "title" => "Total",
              "code" => "total",
              "value" => $order['total_price'],
              "sort_order" => 9
            )
        );
        if($total_shipping>0){
            $order_totals[] =  array(
                "title" => "Shipping",
                "code" => "shipping",
                "value" => $total_shipping,
                "sort_order" => 2
            );
        }
        if($order['total_tax']>0){
            $order_totals[] =  array(
                "title" => "Tax",
                "code" => "tax",
                "value" => $order['total_tax'],
                "sort_order" => 3
            );
        }
        if($order['total_discounts']>0){
            $order_totals[] =  array(
                "title" => "Discount",
                "code" => "discount",
                "value" => $order['total_discounts'],
                "sort_order" => 4
            );
        }
        $post_fields['order_totals'] = $order_totals; 
        return $post_fields;
    
    }

}
