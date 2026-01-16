<?php

namespace App\Http\Controllers\Seller\Channels\Settings;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChannelSetting;
use App\Models\Company;
use App\Models\OrderWebhook;
use App\Jobs\ShopifyOrderSyncJob;
use App\Services\ShopifyGraphQLService;
use Illuminate\Support\Facades\Session;
class ShopifyWebhookController extends Controller
{
    protected $shopifyGraphQLService;
    public function __construct(ShopifyGraphQLService $shopifyGraphQLService)
    {
        $this->shopifyGraphQLService = $shopifyGraphQLService;
    }
    public function handleWebhook(Request $request, $topic)
    {
        $companyId = $request->query('company_id');
        $channelId = $request->query('channel_id');
        $channelSettings = ChannelSetting::where('company_id', $companyId)
        ->where('channel_id', $channelId)
        ->first();
        $shopDomain = $channelSettings->channel_url??'';
        $accessToken = $channelSettings->secret_key??'';
        // Use company_id and channel_id for processing business logic
        // Handle the incoming webhook based on the topic
        $channel_order_id = $request->id;
        $webhookorder = array();
        $webhookorder['company_id'] = $companyId;
        $webhookorder['channel_id'] = $companyId;
        $webhookorder['channel_settings'] = $channelSettings;
        $webhookorder['webhook_data'] = $webhook_data = json_encode($request->all());
        
        switch ($topic) {
            case 'orderscreate':
                $data = [
                    'company_id' => $companyId,          
                    'channel_id' => $channelId,         
                    'channel_order_id' =>$channel_order_id, 
                    'webhook_type' => 'ORDERS_CREATE',
                    'status' => OrderWebhook::STATUS_PENDING,
                    'webhook_data' => $webhook_data, 
                ];                
                // Inserting the record
                $order = OrderWebhook::create($data);
               // ShopifyOrderSyncJob::dispatch($webhookorder);
                break;
            case 'ordersupdated':
                $data = [
                    'company_id' => $companyId,
                    'channel_id' => $channelId,
                    'channel_order_id' =>$channel_order_id,
                    'webhook_type' => 'ORDERS_UPDATED',
                    'status' => OrderWebhook::STATUS_PENDING,
                    'webhook_data' => $webhook_data, 
                ];
                
                // Inserting the record
                $orderWebhook = OrderWebhook::create($data);
                break;
            case 'orderscancelled':
                $data = [
                    'company_id' => $companyId,          
                    'channel_id' => $channelId,         
                    'channel_order_id' =>$channel_order_id, 
                    'webhook_type' => 'ORDERS_CANCELLED',
                    'status' => OrderWebhook::STATUS_PENDING,
                    'webhook_data' => $webhook_data, 
                ];
                
                // Inserting the record
                $orderWebhook = OrderWebhook::create($data);
                break;   
            case 'ordersfulfilled':
                $data = [
                    'company_id' => $companyId,          
                    'channel_id' => $channelId,         
                    'channel_order_id' =>$channel_order_id, 
                    'webhook_type' => 'ORDERS_FULFILLED',
                    'status' => OrderWebhook::STATUS_PENDING,
                    'webhook_data' => $webhook_data, 
                ];
                
                // Inserting the record
                $orderWebhook = OrderWebhook::create($data);
                break; 
            case 'appsubscriptionsupdate':
                Log::info('Processing appsubscriptionsupdate:', $request->all());
                $data = [
                    'company_id' => $companyId,
                    'channel_id' => $channelId,
                    'webhook_data' => $webhook_data, 
                ];          
                break;
            case 'appuninstalled':
                $this->handleAppUninstalled($shopDomain, $accessToken,$companyId);
            break;
        }

        return response()->json(['message' => 'Webhook handled successfully.'], 200);
    }
    protected function handleAppUninstalled($shopDomain, $accessToken,$companyId)
    {
        // Get existing webhooks from Shopify
        $webhookResponse = $this->shopifyGraphQLService->getWebhooks($shopDomain, $accessToken);
        $delete_webhooks = true;
        $company_settings = Company::where('id', $companyId)->first();

        if ($company_settings && $company_settings->subscription_plan !== null && $company_settings->subscription_plan !== 'Free') {
            Company::where('id', $companyId)
                ->update(['subscription_status' => 0]);        
            Session::put("subscription_status", 0);
        }

        Log::info('Processing webhookResponse:', $webhookResponse);
        if (isset($webhookResponse['data']['webhooks']['edges'])) {
            foreach ($webhookResponse['data']['webhooks']['edges'] as $webhookEdge) {
                $webhookId = $webhookEdge['node']['id']; // Get the webhook ID
                
                // Call the deleteWebhook method with the retrieved webhook ID
                $deleteResponse = $this->shopifyGraphQLService->deleteWebhook($shopDomain, $accessToken, $webhookId);
                
                // Check the response to confirm deletion
                if (!isset($deleteResponse['data']['webhookSubscriptionDelete']['deletedWebhookSubscriptionId'])) {
                    $delete_webhooks = false;
                }
            }
        }

        // Prepare the updates based on whether all webhooks were successfully deleted
        $updateData = ['status' => 0];
        if ($delete_webhooks) {
            $updateData['webhooks_create'] = 2;
        }
        ChannelSetting::where('channel_url', $shopDomain)->update($updateData);
    }    
    
    public function customerData(Request $request)
    {
        $topic = $request->header('X-Shopify-Topic');
        $hmac = $request->header('X-Shopify-Hmac-Sha256');
        $data = $request->getContent();

        // Verify the webhook
        if (!$this->verifyWebhook($hmac, $data)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        Log::info("Shopify Webhook Received: {$topic}", $request->all());

        switch ($topic) {
            case 'customers/data_request':
                return $this->handleCustomerDataRequest($request);
            case 'customers/redact':
                return $this->handleCustomerRedact($request);
            case 'shop/redact':
                return $this->handleShopRedact($request);
        }

        return response()->json(['message' => 'Webhook handled'], 200);
    }

    private function verifyWebhook($hmac, $data)
    {
        $calculatedHmac = base64_encode(hash_hmac('sha256', $data, env('SHOPIFY_API_SECRET'), true));
        return hash_equals($hmac, $calculatedHmac);
    }

    private function handleCustomerDataRequest($request)
    {
        // Retrieve customer data and send it to the store owner
        Log::info('Processing customers/data_request:', $request->all());
        return response()->json(['message' => 'Customer data request processed']);
    }

    private function handleCustomerRedact($request)
    {
        // Delete customer data
        Log::info('Processing customers/redact:', $request->all());
        return response()->json(['message' => 'Customer data redacted']);
    }

    private function handleShopRedact($request)
    {
        // Delete store data
        Log::info('Processing shop/redact:', $request->all());
        return response()->json(['message' => 'Shop data redacted']);
    }
}
