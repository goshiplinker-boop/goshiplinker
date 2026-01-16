<?php

namespace App\Http\Controllers\Seller\Channels\ManageOrders;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\OrderService;
use App\Models\ChannelSetting;
use App\Models\PaymentMapping;
use App\Models\BuyerInvoiceSetting;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Http;

class OpencartOrderSyncController extends Controller
{
    public function syncOrders($companyId=null,$syncType="auto")
    {
        $companyId ??= session('company_id');
        $settings = ChannelSetting::where('company_id', $companyId)
            ->where('channel_code', 'opencart')
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
                    $api_key = $setting->client_id;
                    $api_secret = $setting->secret_key;
                    $key_hash = strtoupper(md5($api_key . ':' . $api_secret));
                    $other_details = json_decode($setting->other_details);
                    $fetch_status = $other_details->fetch_status??'pending';
                    $url = rtrim($setting->channel_url, '/') . '/index.php?route=extension/module/parcelmind&service=GetOrders'
                        . '&key_hash=' . $key_hash. '&order_status='.$fetch_status;
                    $response = Http::get($url);
                    $data = $response->json();
                    if (empty($data['orders'])) {
                        $errors[] = "No orders found or invalid response for {$setting->channel_title}.";
                        continue;
                    }
                    $orderService = new OrderService();
                    $paymentMappings = PaymentMapping::where('company_id', $companyId)
                        ->where('channel_id', $setting->channel_id)
                        ->get()
                        ->keyBy('gateway_name');

                    $buyer_invoice_settings = BuyerInvoiceSetting::where('company_id', $companyId)->first();
                    $invoice_type = $buyer_invoice_settings->number_type ?? 'order_number';
                    $invoice_prefix = $invoice_type === 'custom_number' ? $buyer_invoice_settings->prefix ?? '' : '';
                    $invoice_start_from = $invoice_type === 'custom_number' ? $buyer_invoice_settings->start_from ?? '' : '';

                    foreach ($data['orders'] as $order_id => $order) {
                        try {
                            $orderHash = json_decode(base64_decode($order['order_hash']), true);
                            $paymentMethod = $order['payment_type'] ?? '';
                            if (!isset($orderHash['order_info'])) {
                                continue;
                            }

                            $order['channel_id'] = $setting->channel_id;
                            $order['company_id'] = $companyId;
                            $order['paymentGateway'] = $paymentMethod;

                            $paymentCode = $orderService->getPaymentMethod($order, $paymentMappings, $unmappedPaymentCount);
                            if (!$paymentCode) {
                                continue;
                            }

                            $orderFields = $this->mapOrderDetails($order, $setting->channel_id, $paymentCode);

                            $orderFields['invoice_type'] = $invoice_type;
                            $orderFields['invoice_prefix'] = $invoice_prefix;
                            $orderFields['invoice_start_from'] = $invoice_start_from;
                            $existOrder = $orderService->isOrderAlreadyImported($orderFields['vendor_order_number'], $companyId, $setting->channel_id);
                            if ($existOrder) {
                                // if ($existOrder->status_code === 'N') {
                                //     $orderService->updateOrderAddress($existOrder, $orderFields);
                                //     $updatedOrders++;
                                //     continue;
                                // }
                                $alreadyImported++;
                                continue;
                            }
                            $createdOrder = $orderService->createOrder($orderFields, $companyId);
                            if (isset($createdOrder['id'])) {
                                $orderCount++;
                            }
                        } catch (Exception $e) {
                            $errors[] = "Error syncing order ID {$order_id}: {$e->getMessage()}";
                        }
                    }

                    if ($orderCount > 0) {
                        $success[] = $setting->channel_title . " {$orderCount} Orders synced successfully.";
                    } else {
                        $success[] = "No new orders found for {$setting->channel_title} sync.";
                    }

                    if ($updatedOrders > 0) {
                        $success[] = $setting->channel_title . " {$updatedOrders} Orders updated successfully.";
                    }
                } catch (Exception $e) {
                    $errors[] = "Error processing setting ID {$setting->channel_id}: {$e->getMessage()}";
                }
            }
        }

        if (!empty($errors)) {
            $responseMessages['opencart_error'] = implode('; ', $errors);
        }
        if (!empty($success)) {
            $responseMessages['opencart_success'] = implode('; ', $success);
        }

        return redirect()->route('order_list')->with($responseMessages);
    }

    private function mapOrderDetails($order, $channelId, $paymentCode)
    {
        try {
            $vendor_order_id = $order['order_id'];
            $create_at = (new \DateTime($order['date_added']))->format('Y-m-d H:i:s');
            $billingAddress = (array)($order['user_data']['billing_address'] ?? []);
            $shippingAddress = (array)($order['user_data']['shipping_address'] ?? []);
    
            $b_fullname = trim(($billingAddress['firstname'] ?? '') . ' ' . ($billingAddress['lastname'] ?? ''));
            $s_fullname = trim(($shippingAddress['firstname'] ?? '') . ' ' . ($shippingAddress['lastname'] ?? ''));
            $b_email = $order['email'] ?? '';
            $s_email = $order['email'] ?? '';
            $b_phone = $billingAddress['phone'] ?? '';
            $s_phone = $shippingAddress['phone'] ?? '';
    
            $post_fields = [];
            $post_fields['vendor_order_id'] = $vendor_order_id;
            $post_fields['vendor_order_number'] = $order['order_id']; // Fallback if vendor_order_number is missing
            $post_fields['channel_id'] = $channelId;
            $post_fields['channel_order_date'] = $create_at;
            $post_fields['fullname'] = $b_fullname ?: $s_fullname;
            $post_fields['email'] = $b_email ?: $s_email;
            $post_fields['phone_number'] = $b_phone ?: $s_phone;
            $post_fields['order_tags'] = '';
            $post_fields['notes'] = '';
            $post_fields['package_breadth'] = 10;
            $post_fields['package_height'] = 10;
            $post_fields['package_length'] = 10;
            $post_fields['package_dead_weight'] = 0.05;
            $post_fields['currency_code'] = 'INR';
            $post_fields['payment_mode'] = $paymentCode;
            $post_fields['payment_method'] = $order['paymentGateway'] ?? $order['payment_type'];
    
            // Billing address
            $post_fields['b_fullname'] = $b_fullname;
            $post_fields['b_company'] = '';
            $post_fields['b_complete_address'] = trim(($billingAddress['address_1'] ?? '') . ' ' . ($billingAddress['address_2'] ?? ''));
            $post_fields['b_zipcode'] = $billingAddress['zipcode'] ?? '';
            $post_fields['b_phone'] = $billingAddress['phone'] ?? '';
            $post_fields['b_city'] = $billingAddress['city'] ?? '';
            $post_fields['b_landmark'] = null;
            $post_fields['b_state_code'] = $billingAddress['state'] ?? '';
            $post_fields['b_country_code'] = $billingAddress['country'] ?? '';
    
            // Shipping address
            $post_fields['s_fullname'] = $s_fullname;
            $post_fields['s_company'] = '';
            $post_fields['s_complete_address'] = trim(($shippingAddress['address_1'] ?? '') . ' ' . ($shippingAddress['address_2'] ?? ''));
            $post_fields['s_zipcode'] = $shippingAddress['zipcode'] ?? '';
            $post_fields['s_phone'] = $shippingAddress['phone'] ?? '';
            $post_fields['s_city'] = $shippingAddress['city'] ?? '';
            $post_fields['s_landmark'] = null;
            $post_fields['s_state_code'] = $shippingAddress['state'] ?? '';
            $post_fields['s_country_code'] = $shippingAddress['country'] ?? '';
    
            $post_fields['customer_ip_address'] = null;
            $post_fields['financial_status'] = ($paymentCode == 'prepaid') ? 'Paid' : 'Pending';
    
            // Order products
            $lineItems = $order['products'] ?? [];
            $subtotalPrice = 0;
            $post_fields['order_products'] = [];
    
            foreach ($lineItems as $item) {
                $order_products = [];
                $price = (float) $item['price'];
                $quantity = (int) $item['amount']; // 'amount' is used as quantity
                $total_price = $price * $quantity;
    
                $order_products['product_name'] = $item['name'];
                $order_products['sku'] = $item['product_code'] ?? '';
                $order_products['line_item_id'] = null;
                $order_products['unit_price'] = $price;
                $order_products['quantity'] = $quantity;
                $order_products['discount'] = null;
                $order_products['shipping'] = null;
                $order_products['hsn'] = null;
                $order_products['tax_rate'] = 0;
                $order_products['tax_name'] = null;
                $order_products['tax_type'] = 0;
                $order_products['tax_amount'] = 0;
                $order_products['total_price'] = $total_price;
    
                $subtotalPrice += $total_price;
                $post_fields['order_products'][] = $order_products;
            }
    
            $post_fields['sub_total'] = $subtotalPrice;
            $post_fields['order_total'] = $order['order_total'];
    
            // Order totals
            $order_totals = [
                [
                    "title" => "Subtotal",
                    "code" => "sub_total",
                    "value" => $subtotalPrice,
                    "sort_order" => 1,
                ],
                [
                    "title" => "Total",
                    "code" => "total",
                    "value" => $order['order_total'],
                    "sort_order" => 9,
                ],
            ];
    
            if (!empty($order['shipping_cost'])) {
                $order_totals[] = [
                    "title" => "Shipping",
                    "code" => "shipping",
                    "value" => $order['shipping_cost'],
                    "sort_order" => 2,
                ];
            }
    
            if (!empty($order['total_tax'])) {
                $order_totals[] = [
                    "title" => "Tax",
                    "code" => "tax",
                    "value" => $order['total_tax'],
                    "sort_order" => 3,
                ];
            }
    
            if (!empty($order['total_discount'])) {
                $order_totals[] = [
                    "title" => "Discount",
                    "code" => "discount",
                    "value" => $order['total_discount'],
                    "sort_order" => 4,
                ];
            }
    
            $post_fields['order_totals'] = $order_totals;
    
            return $post_fields;
        } catch (\Exception $e) {
            return [];
        }
    }
    
}
