<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\OrderLog;
use App\Models\ShipmentInfo;
use App\Services\OrderLogService;
class ShopifyGraphQLService
{
    public function query($shop, $accessToken, $query, $variables = [])
    {
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
            'Content-Type' => 'application/json',
        ])->post("https://{$shop}/admin/api/".env('SHOPIFY_API_VERSION')."/graphql.json", [
            'query' => $query,
            'variables' => $variables,
        ]);

        return $response;
    }

    public function createWebhook($shop, $accessToken, $topic, $callbackUrl)
    {
        $query = <<<'GRAPHQL'
            mutation webhookSubscriptionCreate($topic: WebhookSubscriptionTopic!, $callbackUrl: URL!) {
                webhookSubscriptionCreate(topic: $topic, webhookSubscription: {callbackUrl: $callbackUrl, format: JSON}) {
                    userErrors {
                        field
                        message
                    }
                    webhookSubscription {
                        id
                    }
                }
            }
        GRAPHQL;

        $variables = [
            'topic' => $topic,
            'callbackUrl' => $callbackUrl,
        ];

        return $this->query($shop, $accessToken, $query, $variables);
    }
    public function getWebhooks($shop, $accessToken)
    {
        $url = "https://{$shop}/admin/api/graphql.json";
        
        $query = <<<GQL
        {
            webhooks(first: 10) {
                edges {
                    node {
                        id
                        topic
                        address
                    }
                }
            }
        }
        GQL;

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
            'Content-Type' => 'application/json',
        ])->post($url, ['query' => $query]);

        return $response->json();
    }

    public function deleteWebhook($shop, $accessToken, $webhookId)
    {
        $url = "https://{$shop}/admin/api/graphql.json";
        
        $query = <<<GQL
        mutation {
            webhookSubscriptionDelete(id: "$webhookId") {
                deletedWebhookSubscriptionId
            }
        }
        GQL;

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
            'Content-Type' => 'application/json',
        ])->post($url, ['query' => $query]);

        return $response->json();
    }
    public function fetchOrders($shopDomain, $accessToken, $query)
    {
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
        ])->post("https://{$shopDomain}/admin/api/2024-10/graphql.json", [
            'query' => $query,
        ]);

        return $response->json();
    }
    public function buildOrderQuery()
    {
        $query = <<<'GRAPHQL'
        query getOrders($ordersAfter: String, $searchQuery: String!) {
            orders(first: 50, after: $ordersAfter, query: $searchQuery) {
                edges {
                    cursor
                    node {
                        id
                        name  
                        email
                        phone
                        clientIp                         
                        cancelledAt
                        cancellation {
                            staffNote
                        }
                        cancelReason
                        confirmationNumber
                        currencyCode
                        createdAt
                        tags
                        poNumber
                        unpaid
                        updatedAt
                        totalWeight
                        totalTipReceived {
                            amount
                            currencyCode
                        }
                        note
                        netPayment
                        paymentGatewayNames
                        taxesIncluded
                        taxExempt
                        totalTax
                        totalShippingPrice
                        subtotalPrice
                        totalPrice
                        totalReceived
                        totalCapturable
                        totalDiscounts
                        discountCode
                        discountCodes
                        displayFinancialStatus
                        displayFulfillmentStatus
                        fulfillable
                        fullyPaid
                        billingAddress {
                            address1
                            address2
                            city
                            company
                            coordinatesValidated
                            country
                            countryCode
                            countryCodeV2
                            firstName
                            id
                            lastName
                            name
                            phone
                            province
                            provinceCode
                            zip
                        }
                        shippingAddress {
                            address1
                            address2
                            city
                            company
                            coordinatesValidated
                            country
                            countryCode
                            countryCodeV2
                            firstName
                            id
                            lastName
                            name
                            phone
                            province
                            provinceCode
                            zip
                        }
                        billingAddressMatchesShippingAddress                            
                        currentSubtotalLineItemsQuantity  
                        customer {
                            lastName
                            firstName
                            email
                            displayName
                            defaultAddress {
                                address1
                                address2
                                city
                                company
                                country
                                countryCode
                                firstName
                                countryCodeV2
                                lastName
                                name
                                phone
                                province
                                provinceCode
                                zip
                            }
                            addresses(first: 1) {
                                address1
                                address2
                                city
                                company
                                country
                                countryCode
                                countryCodeV2
                                firstName
                                id
                                lastName
                                name
                                phone
                                province
                                provinceCode
                                zip
                            }
                        }                                                 
                        displayAddress {
                            zip
                            timeZone
                            provinceCode
                            province
                            phone
                            name
                            lastName
                            id
                            firstName
                            countryCode
                            countryCodeV2
                            country
                            company
                            city
                            address2
                            address1
                        }                           
                        lineItems(first: 50) {
                            edges {
                                node {
                                    id
                                    name
                                    discountedTotal
                                    discountedUnitPrice
                                    quantity
                                    sku
                                    title
                                    taxable
                                    taxLines(first: 5) {
                                        rate
                                        ratePercentage
                                        price
                                        title
                                    }
                                    totalDiscount
                                    discountedTotalSet {
                                        shopMoney {
                                            amount
                                            currencyCode
                                        }
                                    }
                                    discountAllocations {
                                        allocatedAmountSet {
                                            presentmentMoney {
                                                amount
                                                currencyCode
                                            }
                                            shopMoney {
                                                amount
                                                currencyCode
                                            }
                                        }           
                                    }
                                    fulfillableQuantity
                                    fulfillmentStatus                                        
                                    nonFulfillableQuantity
                                    originalTotal
                                    originalUnitPrice
                                    originalTotalSet {
                                        shopMoney {
                                            amount
                                            currencyCode
                                        }
                                    }                                        
                                    product {
                                        tags
                                        status
                                        title
                                    }
                                }
                            }
                            pageInfo {
                                hasNextPage
                                hasPreviousPage
                                endCursor
                                startCursor
                            }
                        }
                        fulfillmentOrders(first: 50) { 
                            nodes {
                                id
                            } 
                        }
                        fulfillments(first: 10) {
                            id
                            displayStatus
                            trackingInfo {
                                company
                                number
                                url
                            }
                        }
                    }
                }
                pageInfo {
                    hasNextPage
                    hasPreviousPage
                    endCursor
                    startCursor
                }
            }
        }
        GRAPHQL;
        return $query;
        

    }
    public function fulfillOrder($fulfillmentOrder,$trackingInfo,$shopUrl,$shopifyAccessToken)
    {   
       
       $endpoint = "https://{$shopUrl}/admin/api/2024-01/graphql.json";

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
        $parcelmind_line_items = $trackingInfo['line_items']??[];
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
                   'number' => $trackingInfo['number'],
                    'url' => $trackingInfo['url'],
                    'company' => $trackingInfo['company'],
                ],
                'notifyCustomer'=>$trackingInfo['notify_customer']?true:false,
            ],
        ];
    
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Shopify-Access-Token' => $shopifyAccessToken,
        ])->post($endpoint, [
            'query' => $query,
            'variables' => $variables,
        ]);
        if ($response->failed()) {
            $orderLog = new OrderLogService();
            $orderLog->createLog(
                $trackingInfo['order_id'],
                $trackingInfo['company_id'],
                $trackingInfo['vendor_order_id'],
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
            $orderLog = new OrderLogService();
            $orderLog->createLog(
                $trackingInfo['order_id'],
                $trackingInfo['company_id'],
                $trackingInfo['vendor_order_id'],
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
    public function fetchFulfillmentOrderId($shopUrl,$shopifyAccessToken,$vendor_orderId)
    {   
        $endpoint = "https://{$shopUrl}/admin/api/2024-01/graphql.json";
        $query = <<<'GRAPHQL'
            query MyQuery($id: ID!) {
                order(id: $id) {
                    fulfillmentOrders(first: 10) {
                        edges {
                            node {
                                id
                                status
                                lineItems(first: 20) {
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
            'id' => 'gid://shopify/Order/' . $vendor_orderId,
        ];

        // Make the HTTP POST request to the Shopify GraphQL API
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Shopify-Access-Token' => $shopifyAccessToken,
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
    public function markPaid($shopUrl,$shopifyAccessToken,$vendor_orderId){
        $endpoint = "https://{$shopUrl}/admin/api/2024-01/graphql.json";
        $query = <<<'GRAPHQL'
            mutation orderMarkAsPaid($input: OrderMarkAsPaidInput!) {
            orderMarkAsPaid(input: $input) {
                userErrors {
                    field
                    message
                }
                order {
                id
                name
                canMarkAsPaid
                displayFinancialStatus
                totalPrice
                totalOutstandingSet {
                    shopMoney {
                    amount
                    currencyCode
                    }
                }
                transactions(first: 10) {
                    id
                    kind
                    status
                    amountSet {
                    shopMoney {
                        amount
                        currencyCode
                    }
                    }
                    gateway
                    createdAt
                }
                }
            }
        }
        GRAPHQL;

        $variables = [
            'input' => [
                'id' => 'gid://shopify/Order/'.$vendor_orderId
            ]
        ];
        $payload = [
            'query' => $query,
            'variables' => $variables,
        ];
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Shopify-Access-Token' => $shopifyAccessToken,
        ])->post($endpoint, $payload);
        $response = $response->json();
        $errors = $response['errors']??'';
        $mark_paid=false;
        if(empty($errors)){
            $data = $response['data']??[];
            $orderMarkAsPaid = $data['orderMarkAsPaid']??[];
            $userErrors = $orderMarkAsPaid['userErrors']??[];
            $errors = $userErrors[0]['message']??'';
            $order = $orderMarkAsPaid['order']??[];
            $displayFinancialStatus = $order['displayFinancialStatus']??'';
            if($displayFinancialStatus=='PAID'){
                $mark_paid = true;

            }

        }
        $res=[];
        if($mark_paid){
            $res['success'] = true;

        }elseif($errors){
            $res['error'] = $errors;
        }
        return $res;
    }
    public function shipmentStatusUpdate($shopDomain,$accessToken,$data=array())
    {
        // GraphQL Mutation
        $query = <<<'GRAPHQL'
        mutation fulfillmentEventCreate($fulfillmentEvent: FulfillmentEventInput!) {
        fulfillmentEventCreate(fulfillmentEvent: $fulfillmentEvent) {
            fulfillmentEvent {
            id
            status
            message
            }
            userErrors {
            field
            message
            }
        }
        }
        GRAPHQL;
        $shopify_status_mappings = [];
        $shopify_status_mappings['SHB']='READY_FOR_PICKUP';
        $shopify_status_mappings['PKS']='READY_FOR_PICKUP';
        $shopify_status_mappings['PKP']='CARRIER_PICKED_UP';
        $shopify_status_mappings['INT']='IN_TRANSIT';
        //$shopify_status_mappings['DEL']='DELAYED';
        $shopify_status_mappings['OFD']='OUT_FOR_DELIVERY';
        $shopify_status_mappings['UND']='ATTEMPTED_DELIVERY';
        $shopify_status_mappings['DEL']='DELIVERED';

        $shipment_status = $data['current_status']??'';
        // Variables
        $variables = [
            "fulfillmentEvent" => [
                "fulfillmentId" => "gid://shopify/Fulfillment/".$data['fulfillment_id'],               
                "message" => "",
                "status" => $shopify_status_mappings[$shipment_status]
            ]
        ];

        // Send request to Shopify GraphQL API
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
            'Accept' => 'application/json',
        ])->post("https://$shopDomain/admin/api/2025-10/graphql.json", [
            'query' => $query,
            'variables' => $variables,
        ]);
        $response = $response->json();
        $errors = $response['errors']??'';
        $resdata = $response['data']??[];
        $fulfillmentEventCreate = $resdata['fulfillmentEventCreate']??[];
        $fulfillmentEvent = $fulfillmentEventCreate['fulfillmentEvent']??[];
        $userErrors = $fulfillmentEventCreatep['userErrors']??[];
        if(!empty($userErrors)){
            $errors = $userErrors[0]['message']??'';

        }
        $res=[];
        if(!empty($fulfillmentEvent)){
            $res['success'] = true;
            ShipmentInfo::where('id', $data['shipment_id'])
            ->update(['store_shipment_status' => $shipment_status]);
        }else{
            $res['error'] = $errors;
        }
        return $res;
    }

}
