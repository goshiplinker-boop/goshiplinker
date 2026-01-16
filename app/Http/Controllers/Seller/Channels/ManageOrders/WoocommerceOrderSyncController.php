<?php

namespace App\Http\Controllers\Seller\Channels\ManageOrders;

use Automattic\WooCommerce\Client;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\OrderService;
use App\Models\ChannelSetting;
use App\Models\PaymentMapping;
use App\Models\Order;
use App\Models\BuyerInvoiceSetting;
use Illuminate\Support\Facades\Log;
use Exception;

class WoocommerceOrderSyncController extends Controller
{
    public function syncOrders($companyId=null,$syncType="auto")
    {
        $companyId ??= session('company_id');
        $settings = ChannelSetting::where('company_id', $companyId)
            ->where('channel_code', 'woocommerce')
            ->where('status', 1)
            ->get();      

        $responseMessages = [];
        $success = [];
        $errors = [];
        if($settings){
            foreach ($settings as $setting) {
                try {
                    $unmappedPaymentCount = 0;
                    $orderCount = 0;
                    $alreadyImported = 0;
                    $updatedOrders = 0;
                    $url = $setting->channel_url;
                    $consumer_key = $setting->client_id;
                    $consumer_secret = $setting->secret_key;
                    $order_status = 'pending,processing';
                    $options = ['version' => 'wc/v3'];
                    $woocommerce = new Client($url, $consumer_key, $consumer_secret, $options);
                    $daysAgo = 500;
                    $mindate = (new \DateTime())->sub(new \DateInterval("P{$daysAgo}D"))->format('Y-m-d\TH:i:s');
                    $page = 1;
                    $perPage = 100;
                    $allOrders = [];
                    do {
                        try {
                            $orders = $woocommerce->get('orders', [
                                'status' => $order_status,
                                'after' => $mindate,
                                'per_page' => $perPage,
                                'page' => $page,
                                'order'=>'asc'
                            ]);
    
                            if (empty($orders)) {
                                break;
                            }
                            
                            $this->syncOrdersForSetting($orders, $setting, $orderCount, $alreadyImported,$updatedOrders, $unmappedPaymentCount, $companyId);
                            $allOrders = array_merge($allOrders, $orders);
                            $page++;
                        } catch (Exception $e) {
                            $errors[] = "Error fetching orders from WooCommerce: " . $e->getMessage();
                            break;
                        }
                    } while (count($orders) === $perPage);
                    $success[] =  $orderCount > 0 ? $setting->channel_title ." ".$orderCount." Orders synced successfully." : "No new orders found for $setting->channel_title sync.";
                    if($updatedOrders>0){
                        $success[] = $setting->channel_title ." ".$updatedOrders." Orders updated successfully.";    
                    }
                } catch (Exception $e) {
                    $errors[] = "Error processing setting ID {$setting->channel_id}: " . $e->getMessage();
                }
                
            }
        }
        // Log errors if any       
        if (!empty($errors)) {
            $responseMessages['woocommerce_error'] = implode('; ', $errors);
        }
        if($success){
            $responseMessages['woocommerce_success'] =implode('; ', $success);
        }
        return redirect()->route('order_list')->with($responseMessages);
    }

    private function syncOrdersForSetting($orders, $setting, &$orderCount, &$alreadyImported,&$updatedOrders, &$unmappedPaymentCount, $companyId)
    {
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
        foreach ($orders as $order) {
            try {
                $orderNode = (array) $order;

                if (isset($orderNode['line_items']) && empty($orderNode['line_items'])) {
                    continue;
                }

                $orderNode['channel_id'] = $setting->channel_id;
                $orderNode['company_id'] = $companyId;
                $paymentGateway = $orderNode['payment_method_title'] ?? '';
                // if (!$paymentGateway) {
                //     continue;
                // }
                $orderNode['paymentGateway'] = $paymentGateway;
                $paymentCode = $orderService->getPaymentMethod($orderNode, $paymentMappings, $unmappedPaymentCount);

                if (!$paymentCode) {
                    continue; // Skip if unmapped payment
                }
                $orderFields = $this->mapOrderDetails($orderNode, $setting->channel_id, $paymentCode);
                $orderFields['invoice_type'] = $invoice_type;
                $orderFields['invoice_prefix'] = $invoice_prefix;
                $orderFields['invoice_start_from'] = $invoice_start_from;
                $existorder = $orderService->isOrderAlreadyImported($orderFields['vendor_order_number'], $companyId, $setting->channel_id);
                if ($existorder) {
                    if($existorder->status_code=='N'){
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
               
                $createdOrder = $orderService->createOrder($orderFields, $companyId);

                if (isset($createdOrder['id'])) {
                    $orderCount++;
                }

            } catch (Exception $e) {
                $errors[] = "Error syncing order ID {$orderNode['id']}: " . $e->getMessage();
            }
        }
    }    

    private function mapOrderDetails($order, $channelId, $paymentCode)
    {
        try {
            $vendor_order_id = $order['id'];
            $payment_method = $order['payment_method_title']?? null;
            $create_at = (new \DateTime($order['date_created']))->format('Y-m-d H:i:s');
            $billingAddress =  (array)$order['billing'];
            $shippingAddress =  (array)$order['shipping'];
            $b_fullname = trim(($billingAddress['first_name'] ?? '') . ' ' . ($billingAddress['last_name'] ?? ''));
            $s_fullname = trim(($shippingAddress['first_name'] ?? '') . ' ' . ($shippingAddress['last_name'] ?? ''));
            $b_email = $billingAddress['email'] ?? '';
            $s_email = $shippingAddress['email'] ?? '';
            $b_phone = $billingAddress['phone'] ?? '';
            $s_phone = $shippingAddress['phone'] ?? '';         
           
            $post_fields=array();
            $post_fields[ 'vendor_order_id'] = $vendor_order_id;
            $post_fields['vendor_order_number'] = $order['number'];
            $post_fields['channel_id'] = $channelId;
            $post_fields['channel_order_date'] = $create_at;
            $post_fields['fullname']  = $b_fullname??$s_fullname;
            $post_fields['email'] = $b_email??$s_email;
            $post_fields['phone_number'] =$b_phone??$s_phone;  
            $post_fields['order_tags'] = $order['tags']??'';
            $post_fields['notes'] = $order['customer_note'];
            $post_fields['package_breadth']  = 10;
            $post_fields['package_height']  = 10;
            $post_fields['package_length']  = 10;
            $post_fields['package_dead_weight'] =0.05;
            $post_fields['currency_code'] = $order['currency'];
            $post_fields['payment_mode']  = $paymentCode;
            $post_fields['payment_method']  = $payment_method;
           
            //Billing address
            $post_fields['b_fullname'] = $b_fullname;
            $post_fields['b_company'] = $billingAddress['company'];
            $post_fields['b_complete_address']  = trim($billingAddress['address_1'].' '.$billingAddress['address_1']);         
            $post_fields['b_zipcode'] = $billingAddress['postcode'];
            $post_fields['b_phone'] = $billingAddress['phone'];
            $post_fields['b_city'] = $billingAddress['city'];
            $post_fields['b_landmark'] = null;
            $post_fields['b_state_code'] = $billingAddress['state'];
            $post_fields['b_country_code'] = $billingAddress['country'];
            //Shipping address
            $post_fields['s_fullname'] = $s_fullname;
            $post_fields['s_company'] = $shippingAddress['company'];
            $post_fields['s_complete_address']  = trim($shippingAddress['address_1'].' '.$shippingAddress['address_1']);   
            $post_fields['s_zipcode'] = $shippingAddress['postcode'];
            $post_fields['s_phone'] = $shippingAddress['phone'];
            $post_fields['s_city'] = $shippingAddress['city'];
            $post_fields['s_landmark'] = null;
            $post_fields['s_state_code'] = $shippingAddress['state'];
            $post_fields['s_country_code'] = $shippingAddress['country'];           
            $post_fields['customer_ip_address'] = $order['customer_ip_address']??null; 
            $post_fields['financial_status'] = ($paymentCode=='prepaid')?'Paid':'Pending';   
            //Order products
            $lineItems =  $order['line_items'];
            $subtotalPrice = 0;
            foreach($lineItems as $lineItem){
                $item = (array)$lineItem;
                $order_products = array();
                $order_products['product_name'] = $item['name'];
                $order_products['sku'] = $item['sku']?:"";
                $order_products['line_item_id'] = null;
                $order_products['unit_price'] = $item['subtotal']/$item['quantity'];
                $order_products['quantity'] = $item['quantity'];
                $order_products['discount'] = null;
                $order_products['shipping'] = null;
                $order_products['hsn'] = null;
                $order_products['tax_rate'] = 0;
                $order_products['tax_name'] = null;
                $order_products['tax_type'] = 0;
                $order_products['tax_amount'] = $item['total_tax'];
                $order_products['total_price'] = $item['subtotal'];
                $subtotalPrice += $item['subtotal'];
                $post_fields['order_products'][]=$order_products;
            }
            $post_fields['sub_total']  = $subtotalPrice;
            $post_fields['order_total']  = $order['total'];
            $order_totals = array(
                array(
                  "title" =>"Subtotal",
                  "code" => "sub_total",
                  "value" =>$subtotalPrice,
                  "sort_order" => 1
                ),                                         
                array(
                  "title" => "Total",
                  "code" => "total",
                  "value" => $order['total'],
                  "sort_order" => 9
                )
            );
            if($order['shipping_total']>0){
                $order_totals[] =  array(
                    "title" => "Shipping",
                    "code" => "shipping",
                    "value" => $order['shipping_total'],
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
            if($order['discount_total']>0){
                $order_totals[] =  array(
                    "title" => "Discount",
                    "code" => "discount",
                    "value" => $order['discount_total'],
                    "sort_order" => 4
                );
            }
            $post_fields['order_totals'] = $order_totals; 
            return $post_fields;
        } catch (Exception $e) {
            return [];
        }
    }
}
