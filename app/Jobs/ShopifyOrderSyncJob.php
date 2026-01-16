<?php 

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\OrderService;
use App\Models\OrderWebhook;
use App\Models\ChannelSetting;
use App\Models\PaymentMapping;
use App\Models\Order;
use Exception;

class ShopifyOrderSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $webhookOrder;

    /**
     * Create a new job instance.
     * 
     * @param array $webhookOrder
     */
    public function __construct(array $webhookOrder)
    {
        $this->webhookOrder = $webhookOrder;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $companyId = $this->webhookOrder['company_id'];
        $channelId = $this->webhookOrder['channel_id'];
        $settings = $this->webhookOrder['channel_settings'];
        $webhookData = json_decode($this->webhookOrder['webhook_data'], true);

        if (!$webhookData) {
            Log::error('Invalid webhook data format.');
            return;
        }

        try {
            // Fetch payment mappings for this channel
            $paymentMappings = PaymentMapping::where('company_id', $companyId)
                ->where('channel_id', $channelId)
                ->get()
                ->keyBy('gateway_name');

            $existingOrder = $this->isOrderAlreadyImported($webhookData['id'], $companyId, $channelId);

            if ($existingOrder) {
                $this->updateOrderStatus($webhookData['id'], $companyId, $channelId, $existingOrder->id, 1);
            } else {
                $paymentCode = $this->getPaymentMethod($webhookData, $paymentMappings, $unmappedPaymentCount);
                
                if (!$paymentCode) {
                    $this->updateOrderStatus($webhookData['id'], $companyId, $channelId, null, 2);
                    return;
                }

                if ($this->shouldSkipOrderBasedOnTags($webhookData, $settings)) {
                    $this->updateOrderStatus($webhookData['id'], $companyId, $channelId, null, 2);
                    return;
                }

                $orderService = new OrderService($settings['secret_key'], $settings['channel_url']);
                $postFields = $this->mapOrderDetails($webhookData);
                $createOrder = $orderService->createOrder($postFields, $companyId);

                if ($createOrder && isset($createOrder->id)) {
                    $this->updateOrderStatus($webhookData['id'], $companyId, $channelId, $createOrder->id, 1);
                }
            }
        } catch (Exception $e) {
            Log::error("Error processing Shopify order: " . $e->getMessage());
        }
    }

    /**
     * Check if the order has already been imported.
     * 
     * @param string $vendorOrderId
     * @param int $companyId
     * @param int $channelId
     * @return Order|null
     */
    private function isOrderAlreadyImported($vendorOrderId, $companyId, $channelId)
    {
        return Order::where('vendor_order_id', $vendorOrderId)
            ->where('company_id', $companyId)
            ->where('channel_id', $channelId)
            ->first();
    }

    /**
     * Get payment method based on mappings.
     * 
     * @param array $order
     * @param Collection $paymentMappings
     * @param int $unmappedPaymentCount
     * @return string|null
     */
    private function getPaymentMethod($order, $paymentMappings, &$unmappedPaymentCount)
    {
        $paymentGateway = $order['payment_gateway_names'][0] ?? null;
        if (!$paymentGateway) {
            return null;
        }

        $paymentMapping = $paymentMappings[$paymentGateway] ?? null;
        if (!$paymentMapping) {
            PaymentMapping::updateOrCreate(
                [
                    'channel_id' => $order['channel_id'],
                    'company_id' => $order['company_id'],
                    'gateway_name' => $paymentGateway,
                ],
                ['status' => 0]
            );
            $unmappedPaymentCount++;
            return null;
        }

        return $paymentMapping->status ? $paymentMapping->payment_mode : null;
    }

    /**
     * Check if the order should be skipped based on tags.
     * 
     * @param array $order
     * @param array $setting
     * @return bool
     */
    private function shouldSkipOrderBasedOnTags($order, $setting)
    {
        if (empty($setting['other_details']['order_tags']) || empty($order['tags'])) {
            return false;
        }

        $requiredTags = array_map('trim', explode(',', strtolower($setting['other_details']['order_tags'])));
        $orderTags = array_map('trim', explode(',', strtolower($order['tags'])));

        return count(array_intersect($requiredTags, $orderTags)) === 0;
    }

    /**
     * Map order details to fields expected by OrderService.
     * 
     * @param array $order
     * @return array
     */
    private function mapOrderDetails($order)
    {
        $createDate = (new \DateTime($order['createdAt']))->format('Y-m-d H:i:s');
        $billingAddress = $order['billing_address'] ?? [];
        $shippingAddress = $order['shipping_address'] ?? [];
        $customer = $order['customer'] ?? [];
        $fullname = trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''));

        return [
            'vendor_order_id' => $order['id'],
            'vendor_order_number' => ltrim($order['name'], '#'),
            'channel_id' => $order['channel_id'],
            'channel_order_date' => $createDate,
            'fullname' => $fullname,
            'email' => $order['email'] ?? ($customer['email'] ?? ''),
            'phone_number' => $order['phone'] ?? ($customer['phone'] ?? ''),
            'notes' => $order['note'],
            'package_breadth' => 10,
            'package_height' => 10,
            'package_length' => 10,
            'package_dead_weight' => ($order['total_weight'] ?? 0) > 0 ? $order['total_weight'] / 1000 : 0.05,
            'currency_code' => $order['currency_code'],
            'payment_mode' => 'cod',
            'payment_method' => $order['payment_gateway_names'][0] ?? '',
            'sub_total' => $order['subtotal_price'],
            'order_total' => $order['total_price'],
            'b_fullname' => $billingAddress['name'] ?? '',
            'b_company' => $billingAddress['company'] ?? '',
            'b_complete_address' => trim(($billingAddress['address1'] ?? '') . ' ' . ($billingAddress['address2'] ?? '')),
            'b_zipcode' => $billingAddress['zip'] ?? '',
            'b_phone' => $billingAddress['phone'] ?? '',
            'b_city' => $billingAddress['city'] ?? '',
            'b_state_code' => $billingAddress['province_code'] ?? '',
            'b_country_code' => $billingAddress['country_code'] ?? '',
            's_fullname' => $shippingAddress['name'] ?? '',
            's_company' => $shippingAddress['company'] ?? '',
            's_complete_address' => trim(($shippingAddress['address1'] ?? '') . ' ' . ($shippingAddress['address2'] ?? '')),
            's_zipcode' => $shippingAddress['zip'] ?? '',
            's_phone' => $shippingAddress['phone'] ?? '',
            's_city' => $shippingAddress['city'] ?? '',
            's_state_code' => $shippingAddress['province_code'] ?? '',
            's_country_code' => $shippingAddress['country_code'] ?? '',
            'financial_status' => strtolower($order['financial_status'] ?? ''),
            'order_products' => $this->mapOrderProducts($order['line_items'] ?? []),
            'order_totals' => $this->calculateOrderTotals($order),
        ];
    }

    /**
     * Update order status in OrderWebhook.
     * 
     * @param int $channelOrderId
     * @param int $companyId
     * @param int $channelId
     * @param int|null $importedOrderId
     * @param int $status
     */
    private function updateOrderStatus($channelOrderId, $companyId, $channelId, $importedOrderId, $status)
    {
        OrderWebhook::updateOrCreate(
            [
                'channel_order_id' => $channelOrderId,
                'company_id' => $companyId,
                'channel_id' => $channelId,
            ],
            [
                'imported_order_id' => $importedOrderId,
                'status' => $status,
            ]
        );
    }

    /**
     * Map line items to order products format.
     * 
     * @param array $lineItems
     * @return array
     */
    private function mapOrderProducts(array $lineItems)
    {
        return array_map(function ($item) {
            return [
                'sku' => $item['sku'] ?? '',
                'name' => $item['name'] ?? '',
                'quantity' => $item['quantity'] ?? 0,
                'unit_price' => $item['price'] ?? 0,
                'total_price' => $item['price'] * $item['quantity'],
            ];
        }, $lineItems);
    }

    /**
     * Calculate order totals for each line item.
     * 
     * @param array $order
     * @return array
     */
    private function calculateOrderTotals($order)
    {
        $totals = [];
        foreach ($order['total_line_items_price_set'] as $item) {
            $totals[] = [
                'title' => $item['title'] ?? '',
                'code' => strtolower($item['code'] ?? ''),
                'value' => $item['price_set']['presentment_money']['amount'] ?? 0,
                'sort_order' => 1, // assuming a default sort order
            ];
        }
        return $totals;
    }
}
