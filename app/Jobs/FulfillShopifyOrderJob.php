<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\ShipmentInfo;
class FulfillShopifyOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $shopifyAccessToken;
    protected $shopUrl;
    protected $orderId;
    protected $trackingInfo;
    protected $orderLog;
    /**
     * Create a new job instance.
     *
     * @param string $shopifyAccessToken
     * @param string $shopUrl
     * @param string $orderId
     * @param array $trackingInfo
     */
    public function __construct($shopifyAccessToken, $shopUrl, $orderId, $trackingInfo,$orderLog)
    {
        $this->shopifyAccessToken = $shopifyAccessToken;
        $this->shopUrl = $shopUrl;
        $this->orderId = $orderId;
        $this->trackingInfo = $trackingInfo;
        $this->orderLog = $orderLog;
    }


    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            // Step 1: Fetch Fulfillment Order ID
            $fulfillOrder = $this->fetchFulfillmentOrderId();  
            $updatestatus=3;  
             if (isset($fulfillOrder['error'])) {
                if($fulfillOrder['error']=='already fulfilled'){
                    $updatestatus = 1;
                }                
                $this->orderLog->createLog(
                    $this->trackingInfo['order_id'],
                    $this->trackingInfo['company_id'],
                    $this->trackingInfo['vendor_order_id'],
                    'fulfillment',
                    json_encode($fulfillOrder['request']),
                    $fulfillOrder['response'],
                    false);
            }else{
                // Step 2: Fulfill the Order
                $fulfillmentId = $this->fulfillOrder($fulfillOrder);
                if(!empty($fulfillmentId)){
                    $updatestatus=1;                    
                }                
            }
            ShipmentInfo::where('order_id',$this->trackingInfo['order_id'])
                ->update(['fulfillment_status'=>$updatestatus]);
    
        } catch (\Exception $e) {
            ShipmentInfo::where('order_id',$this->trackingInfo['order_id'])
                ->update(['fulfillment_status'=>3]);
                $this->orderLog->createLog(
                $this->trackingInfo['order_id'],
                $this->trackingInfo['company_id'],
                $this->trackingInfo['vendor_order_id'],
                'fulfillment',
                null,
                json_encode([$e->getMessage()]),
                false
            );
        }
    }  
    public function fulfillOrder($fulfillmentOrder)
    {   
       $endpoint = "https://{$this->shopUrl}/admin/api/2024-01/graphql.json";

        $query =  <<<'GRAPHQL'
        mutation CreateFulfillment($fulfillment: FulfillmentV2Input!) {
            fulfillmentCreateV2(fulfillment: $fulfillment) {
                fulfillment {
                    id
                    displayStatus
                    status
                }
                userErrors {
                    field
                    message
                }
            }
        }
        GRAPHQL;
        $fulfillmentOrderId = $fulfillmentOrder['id'];
        $line_items = $fulfillmentOrder['lineItems'];
        $parcelmind_line_items = $this->trackingInfo['line_items']??[];
        $fulfillable_items = [];        
        foreach($line_items as $line_item_id=>$line_item){
            if(isset($parcelmind_line_items[$line_item_id])){
                $fulfillable_items[] = [
                    "id" => $line_item['id'],
                    "quantity" => $parcelmind_line_items[$line_item_id]
                ];
            }
        }
        $variables = [
            'fulfillment' => [
                'lineItemsByFulfillmentOrder' => [
                    'fulfillmentOrderId' => $fulfillmentOrderId,
                    'fulfillmentOrderLineItems' => $fulfillable_items,
                ],
                'trackingInfo' => [
                    'number' => $this->trackingInfo['number'],
                    'url' => $this->trackingInfo['url'],
                    'company' => $this->trackingInfo['company'],
                ],
                'notifyCustomer'=>$this->trackingInfo['notify_customer'],
            ],
        ];
    
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Shopify-Access-Token' => $this->shopifyAccessToken,
        ])->post($endpoint, [
            'query' => $query,
            'variables' => $variables,
        ]);
        if ($response->failed()) {
            $this->orderLog->createLog(
                $this->trackingInfo['order_id'],
                $this->trackingInfo['company_id'],
                $this->trackingInfo['vendor_order_id'],
                'fulfillment',
                json_encode([
                    'query' => $query,
                    'variables' => $variables,
                ]),
                $response->body(),
                false
            ); 
            return null;           
        }
        $data = $response->json();  
        if (!empty($data['errors']) || !empty($data['data']['fulfillmentCreateV2']['userErrors'])) {
            $this->orderLog->createLog(
                $this->trackingInfo['order_id'],
                $this->trackingInfo['company_id'],
                $this->trackingInfo['vendor_order_id'],
                'fulfillment',
                json_encode([
                    'query' => $query,
                    'variables' => $variables,
                ]),
                $response->body(),
                false
            );      
           return null;
        }
        return $data['data']['fulfillmentCreateV2']['fulfillment']['id'] ?? null;
    }
    public function fetchFulfillmentOrderId()
    {   
        $endpoint = "https://{$this->shopUrl}/admin/api/2024-01/graphql.json";
        $query = <<<'GRAPHQL'
            query MyQuery($id: ID!) {
                order(id: $id) {
                    fulfillmentOrders(first: 10) {
                        edges {
                            node {
                                id
                                status
                                lineItems(first: 50) {
                                    edges {
                                        node {
                                            id
                                            remainingQuantity
                                            lineItem {
                                                id
                                                sku
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    fulfillable
                }
            }
        GRAPHQL;

        // Set the variables including the correct global ID format for Shopify orders
        $variables = [
            'id' => 'gid://shopify/Order/' . $this->orderId,
        ];

        // Make the HTTP POST request to the Shopify GraphQL API
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Shopify-Access-Token' => $this->shopifyAccessToken,
        ])->post($endpoint, [
            'query' => $query,
            'variables' => $variables,
        ]);
        $fulfillOrder = array();
        $fulfillOrder['request'] = [
            'query' => $query,
            'variables' => $variables,
        ];
        $fulfillOrder['response'] = $response->body();
        // Check if the response failed
        if ($response->failed()) {
            $fulfillOrder['error'] = 'some thing went wrong';
        }
        $data = $response->json();
          
        if (!empty($data['errors'])) {
            $fulfillOrder['error'] = $data['errors'];
        }
        $fulfillmentOrders = $data['data']['order']??[];
        if (empty($fulfillmentOrders)) {
            $fulfillOrder['error'] = "Order is not found.";
        }
        $fulfillmentOrders = $fulfillmentOrders['fulfillmentOrders']['edges']??[];
        if (empty($fulfillmentOrders)) {
            $fulfillOrder['error'] = "fullfilemnt id not found";
        }
        if(!isset($fulfillOrder['error'])){
            $fulfillmentOrder_id=0;
            $line_items = [];
            $open_status=0;
            if(!empty($fulfillmentOrders)){
                foreach($fulfillmentOrders as $fulfillmentOrder){
                    $node = $fulfillmentOrder['node']??[];
                    if($node['status']=='OPEN'){
                        $open_status=1;
                        $lineItems = $node['lineItems']['edges']??[];
                        foreach($lineItems as $lineItem){
                            $node_items = $lineItem['node']??[];
                            $line_item_id = $node_items['lineItem']['id']??'';
                            $line_item_id = basename($line_item_id);
                            $line_items[$line_item_id] = ['id'=>$node_items['id'],'quantity'=>$node_items['remainingQuantity']];
                        }
                        $fulfillmentOrder_id = $node['id'];
                        break;
                    }

                }
            }
            if(!empty($line_items)){
                $fulfillOrder['id'] = $fulfillmentOrder_id;
                $fulfillOrder['lineItems'] = $line_items;
            }else{
                $fulfillOrder['error'] = "already fulfilled";
            }
            

        }
        return $fulfillOrder;
    }

}