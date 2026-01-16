<?php

namespace App\Http\Controllers\Seller\SellerPayment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SellerPaymentHistory;
use Illuminate\Support\Facades\Session;
use App\Models\Plan;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\ChannelSetting;

class ShopifyPaymentController extends Controller
{
    public function createApplicationCharge(Request $request)
    {
        $company_id = session('company_id');

        $plan_id = $request->input('plan_id') ?? 0;
        $channel_id = $request->input('channel_id') ?? 0;
        $duration_months = $request->input('duration_months') ?? 0;
        $receipt = 'receipt_' . time();

        if (empty($plan_id) || empty($duration_months)) {
            session()->flash('error', 'Invalid input parameters.');
            return response()->json(['error' => 'Invalid input parameters'], 400);
        }

        $channel_settings = ChannelSetting::where("channel_id", $channel_id)
            ->where("company_id", $company_id)
            ->where("status", 1)
            ->first();

        if (empty($channel_settings)) {
            session()->flash('error', 'Channel does not exist');
            return response()->json(['error' => 'Invalid input parameters'], 400);
        }

        $shop = $channel_settings->channel_url;
        $api_key = $channel_settings->client_id;
        $secret_key = $channel_settings->secret_key;

        $plan = Plan::with("durations")
            ->where('id', $plan_id)
            ->first();
        
        $plan_durations = $plan->durations->where('duration_months', $duration_months)->first();
        $amount = $plan_durations->total_amount + $plan->setup_fee;
        $payment_order_id = $company_id . $plan_id . rand(0, 99999);
        
        SellerPaymentHistory::create([
            'company_id' => $company_id,
            'payment_order_id' => $payment_order_id,
            'amount' => $amount,
            'gateway' => 'Shopify',
            'status' => 'pending',
        ]);

        Subscription::create([
            'company_id' => $company_id,
            'payment_order_id' => $payment_order_id,
            'plan_id' => $plan_id,
            'paid_amount' => $amount,
            'purchased_credits' => $plan_durations->shipment_credits,
            'expiry_date' => date('Y-m-d', strtotime('+ ' . $duration_months . ' months')),
        ]);

        $app_charge_url = "https://$shop/admin/api/2024-10/graphql.json";

        if($duration_months==1){
            $recruring_plan = 'EVERY_30_DAYS';

        }else{
            $recruring_plan = 'ANNUAL';
        }
        // Define the mutation
        $mutation = <<<GQL
        mutation AppSubscriptionCreate(
          \$name: String!,
          \$lineItems: [AppSubscriptionLineItemInput!]!,
          \$returnUrl: URL!,
          \$trialDays: Int,
          \$test: Boolean
        ) {
          appSubscriptionCreate(
            name: \$name,
            returnUrl: \$returnUrl,
            lineItems: \$lineItems,
            trialDays: \$trialDays,
            test: \$test
          ) {
            userErrors {
              field
              message
            }
            appSubscription {
              id
            }
            confirmationUrl
          }
        }
        GQL;
    
        // Define variables for the subscription
        $variables = [
            'name' => 'Parcelmind Subscription',
            "returnUrl" => route('shopify.payment.callback', [
                        'order_id' => $payment_order_id,
                        'plan_id' => $plan_id,
                        'company_id' => $company_id,
                        'channel_id' => $channel_id
                    ]),
            'lineItems' => [
                [
                    'plan' => [
                        'appRecurringPricingDetails' => [
                            'price' => [
                                'amount' =>$amount / env('USD_PRICE', 1),
                                'currencyCode' => 'USD',
                            ],
                            'interval' => $recruring_plan,
                        ],
                    ],
                ],
            ],
            'trialDays' => 7,
            'test' => true // Set false for live subscription
        ];
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $secret_key,
            'Content-Type' => 'application/json',
        ])->post($app_charge_url,  [
            'query' => $mutation,
            'variables' => $variables,
        ]);
        if ($response->failed()) {
            Log::error('Shopify API Error: ' . $response->body());
            return redirect()->route("subscription_plans")->with("error", "Shopify API request failed");
        }
        $charge = $response->json();
        if (!empty($charge['data']['appSubscriptionCreate']['userErrors'])) {
            return redirect()->route("subscription_plans")->with("error", "Failed to create application charge");
        }    
        // Get the confirmation URL
        return redirect()->away($charge['data']['appSubscriptionCreate']['confirmationUrl']);
       
    }

    public function paymentCallback(Request $request)
    {
        $chargeId = intval($request->query('charge_id', 0));
        $channel_id = intval($request->query('channel_id', 0));
        $plan_id = intval($request->query('plan_id', 0));
        $order_id = intval($request->query('order_id', 0));
        $company_id = intval($request->query('company_id', 0));

        if (!$chargeId || !$channel_id || !$company_id) {
            return redirect()->route("subscription_plans")->with("error", "Invalid input parameters");
        }

        $channel_settings = ChannelSetting::where("channel_id", $channel_id)
            ->where("company_id", $company_id)
            ->where("status", 1)
            ->first();

        if (!$channel_settings) {
            Log::error("Channel does not exist: channel_id={$channel_id}, company_id={$company_id}");
            return redirect()->route("subscription_plans")->with("error", "Channel does not exist");
        }

        $shop = $channel_settings->channel_url;
        $api_key = $channel_settings->client_id;
        $secret_key = $channel_settings->secret_key;
        $appsubcriptions = $this->getAppSubscriptions($shop, $secret_key);
        if(isset($appsubcriptions['errors']['0'])){
            return redirect()->route("subscription_plans")->with("error", $appsubcriptions['errors']['0']['message']??'Failed to fetch charge details');

        }
        $currentAppInstallation = $appsubcriptions['data']['currentAppInstallation']['activeSubscriptions']??[];
        $recharge_chargeId = $currentAppInstallation['0']['id']??'';
        $charge_status = $currentAppInstallation['0']['status']??'';
        $recharge_chargeId = ($recharge_chargeId)?basename($recharge_chargeId):'';
        if ($recharge_chargeId !=$chargeId || $charge_status !='ACTIVE' ){
            return redirect()->route("subscription_plans")->with("error", "Payment verification failed");
        }
        
        if ($charge_status == 'ACTIVE') {
            SellerPaymentHistory::where('payment_order_id', $order_id)
                ->where("company_id", $company_id)
                ->update(['txn_id' => $chargeId, 'status' => 'success']);

            $subscription = Subscription::where('payment_order_id', $order_id)
                ->update(['payment_status' => 1]);
            $plan = Plan::where('id', $plan_id)->first();
            if($plan){
                Company::where('id', $company_id)->update(['subscription_plan' => $plan->name,'subscription_status' => 1]);
                Session::put("subscription_plan", $plan->name);
                Session::put("subscription_status", 1);
            }

            return redirect()->route("subscription_plans")->with("success", "Payment verified successfully");
        }

        SellerPaymentHistory::where('payment_order_id', $order_id)
            ->update(['txn_id' => $chargeId ?: null, 'status' => 'failed']);

        Subscription::where('payment_order_id', $order_id)
            ->update(['payment_status' => 0]);
        
        return redirect()->route("subscription_plans")->with("error", "Payment verification failed");
    }
    public function getAppSubscriptions($shop, $accessToken)
    {
        $url = "https://{$shop}/admin/api/".env('SHOPIFY_API_VERSION')."/graphql.json";

        $query = <<<GRAPHQL
        {
            currentAppInstallation {
                activeSubscriptions {
                    id
                    name
                    status
                    currentPeriodEnd
                }
            }
        }
        GRAPHQL;

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
            'Content-Type' => 'application/json',
        ])->post($url, ['query' => $query]);

        return $response->json();
    }
}
