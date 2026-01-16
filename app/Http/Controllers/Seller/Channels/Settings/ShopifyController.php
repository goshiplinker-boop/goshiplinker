<?php

namespace App\Http\Controllers\Seller\Channels\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChannelSetting;
use App\Models\CourierSetting;
use App\Models\Channel;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use App\Services\ShopifyGraphQLService;
use App\Models\PaymentMapping;
use App\Models\ManageTrackingPage;
use App\Models\CourierMapping;
class ShopifyController extends Controller
{   
    protected $shopifyGraphQLService;
    public function __construct(ShopifyGraphQLService $shopifyGraphQLService)
    {
        $this->shopifyGraphQLService = $shopifyGraphQLService;
    }
    public function begin(Request $request)
    {
        // Get the shop and other query parameters from the request
        $shop = $request->query('shop');
        $code = $request->query('code');
        $signature = $request->query('signature');
        $state = $request->query('state');
        $hmac = $request->query('hmac');

        // Shopify API credentials from the environment
        $client_id = env('SHOPIFY_API_KEY');
        $scopes = env('SHOPIFY_SCOPES');
        $redirect_uri = route('shopify.callback');  // Laravel route for the callback

        // Construct the install/approval URL
        $install_url = "https://{$shop}/admin/oauth/authorize?client_id={$client_id}&scope={$scopes}&redirect_uri=" . urlencode($redirect_uri);

        // Redirect to Shopify install/approval URL
        return redirect($install_url);
    }
    public function callback(Request $request){
         $params = $request->all();
        $hmac = $params['hmac'] ?? '';
        unset($params['hmac']);
        ksort($params);
         $client_id = env('SHOPIFY_API_KEY');
        $client_secret = env('SHOPIFY_API_SECRET');
        $computed_hmac = hash_hmac('sha256', http_build_query($params), $client_secret);
        if (hash_equals($hmac, $computed_hmac)) {
            $query = [
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'code' => $params['code'],
            ];

            $access_token_url = "https://{$params['shop']}/admin/oauth/access_token";
            $response = Http::post($access_token_url, $query);

            if ($response->successful()) {
                $access_token = $response->json('access_token');
                $shopInfoResponse = $this->shopifyCall($access_token, str_replace(".myshopify.com", "", $params['shop']), "/admin/shop.json");

                $shop = $shopInfoResponse['shop'] ?? [];
                $email = $shop['email'] ?? 'support+' . time() . '@parcelmind.com';
                $phone = $shop['phone'] ?? time();
                $country_code = $shop['country_code'] ?? 'IN';
                $shop_owner = explode(' ', $shop['shop_owner'] ?? '');
                $first_name = $shop_owner[0] ?? $params['shop'];
                $last_name = $shop_owner[1] ?? '';
                $channel_settings = ChannelSetting::WHERE('channel_url',$params['shop'])->first();
                $company_id = (Auth::check())? session('company_id'):0;
                if(empty($company_id) && $channel_settings && $channel_settings->company_id){
                    $company_id = $channel_settings->company_id;
                    $user = User::Where('company_id',$channel_settings->company_id)->first();
                    Auth::login($user); // Log in user
                    Session::put('role_id', $user->role_id);
                    Session::put('company_id', $company_id);
                    
                }
                if(empty($company_id)){
                     // Create the company
                    $company_exist = Company::Where('company_email_id',$email)->first();
                    if($company_exist){
                        $email = time().$email;
                    }
                    $companydata = array();
                    $companydata['legal_registered_name']='';
                    $companydata['phone_number']=$phone;
                    $companydata['company_email_id'] = $email;
                    $companydata['country_code'] = $country_code;
                    $company = Company::create($companydata);
                    $company_id = $company->id;
            
                    // Create the user
                    $userdata = array();
                    $userdata['name'] = $first_name.($last_name?(' '.$last_name):'');
                    $userdata['email'] = $email;
                    $userdata['password'] = bcrypt($phone);
                    $userdata['company_id'] = $company_id;
                    $userdata['role_id'] = 2;
                    $user = User::create($userdata);
                    Auth::login($user); // Log in user
                    Session::put('role_id', $user->role_id);
                    Session::put('company_id', $company_id);
                    
                }
                $channel_settings = ChannelSetting::WHERE('channel_url',$params['shop'])->first();
                if($channel_settings && $channel_settings->company_id != $company_id){
                    $shopify_url = $channel_settings->channel_url;
                    $existing_company = Company::find($channel_settings->company_id);
                    $existing_email = $existing_company->company_email_id;
                    return redirect()->route('channels_list')->withErrors("The Shopify store ({$shopify_url}) is already associated with a different account ({$existing_email}).");
                }
                $domain = str_replace('.myshopify.com', '', $params['shop'] ?? '');
                $jsondata = [
                    'website_url' => 'https://' . ($params['shop'] ?? ''),
                    'heading_title' => 'Track Your Order',
                    'heading_sub_title' => 'Enter your Order ID or Tracking Number',
                    'tracking_type' => ["order_number"],
                    'support_email_address' => $shop['email'] ?? null,
                    'support_contact_number' => $shop['phone'] ?? null,
                    'youtube_video' => null,                   
                    'promotional_banner' => null,
                    'website_logo' => null,
                    'theme_color' => null,
                    'announcement' => null,
                    'announcement_url' => null,
                    'promotional_url' => null,
                ];
                $trackmanage = [
                    'company_id' => $company_id,  // Ensure this field exists in the table                    
                    'website_domain' => $domain,
                    'custom_style_script' => null,
                    'json_data'=>json_encode($jsondata),
                    'status' => 1,
                ];
                // Use firstOrCreate to check and insert if it doesn't exist
                $manageTrackingPage = ManageTrackingPage::firstOrCreate(
                    ['company_id' => $company_id], // Unique condition
                    $trackmanage
                );

                if ($channel_settings) {
                    $channelId = $channel_settings->channel_id;
                } else {
                    $channel = Channel::create([
                        'company_id' => $company_id,
                        'parent_id' => '2',  // Assuming the parent ID is static
                        'name' => 'Shopify',
                        'status' =>1,
                        'image_url' => 'images/channels/shopify.png', // Set default image
                    ]);
                    $channelId = $channel->id;
                }
               
                 // Save or update channel data in the database
                $channel_settings = ChannelSetting::updateOrCreate(
                    ['channel_url' => $params['shop']], // Ensure this is a valid URL or identifier
                    [
                        'channel_title' => 'Shopify Store',
                        'client_id' => $client_id,
                        'secret_key' => $access_token,
                        'brand_name' =>'Shopify',
                        'company_id' => $company_id, // Update as needed
                        'channel_id' => $channelId, // Update as needed
                        'channel_code' => 'shopify',
                        'status' => 1,
                    ]
                );
                //if($channel_settings->webhooks_create !=1){
                    $this->registerWebhooks($params['shop'], $access_token, $company_id, $channelId);   
                    $channel_settings->webhooks_create=1;
                    $channel_settings->save();                
                //}
                return redirect()->route('channels_list')->with('success', 'Shopify setting created successfully.');
            }
        } else {
            abort(403, 'HMAC verification failed');
        }
    }
    private function shopifyCall($accessToken, $shop, $endpoint, $query = [], $method = 'GET')
    {
        $url = "https://{$shop}.myshopify.com{$endpoint}";
        $headers = ['X-Shopify-Access-Token' => $accessToken];

        return Http::withHeaders($headers)->{$method}($url, $query)->json();
    }
    public function create()
    {
       return view('seller.channels.settings.shopify.create');
    }
    public function edit(string $id)
    {   
        $companyId = session('company_id');
        $shopify = ChannelSetting::where(['channel_id'=>$id,'company_id'=>$companyId])->firstOrFail();
        $shopify->other_details = ($shopify->other_details)?json_decode($shopify->other_details,true):array();
        $payment_types = PaymentMapping::where('company_id', $companyId)->where('channel_id', $id)->get();
        $courier_types = CourierMapping::where('company_id', $companyId)->where('channel_id', $id)->get();
        $couriers = CourierSetting::where('company_id', $companyId)->where('status',1)->get();
        return view('seller.channels.settings.shopify.edit',['shopify'=>$shopify,'payment_types'=>$payment_types,'courier_types'=>$courier_types,'couriers'=>$couriers]); 
    }
    public function update(Request $request, $id)
    {
        // Retrieve the company ID from session
        $companyId = session('company_id');
        // Validate the request data
        $validatedData = $request->validate([
            'channel_title' => 'required|string|max:50',
            'client_id' => 'required|string|max:255',
            'secret_key' => 'required|string|max:255',
            'brand_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'brand_name' => 'required|string|max:20',
            'channel_url' => 'required|string',
            'other_details' => 'required|array',
            'other_details.order_tags' => 'nullable|string',
            'other_details.is_address_same' => 'required|numeric',
            'other_details.pull_update_orders' => 'required|numeric',
            'other_details.notify_customer' => 'required|numeric',
            'other_details.fetch_status' => 'required|string', 
            'other_details.auto_sync' => 'nullable|boolean',
            'status' => 'required|boolean',
        ]);
        
        // Find the existing channel entry
        $channel = Channel::findOrFail($id);        
        // Update the channel entry
        $channel->update([
            'name' => $validatedData['channel_title'],
            'status' => $validatedData['status'],
        ]);

        // Prepare post data
        $post_data = $validatedData;
        $post_data['company_id'] = $companyId;
        $post_data['channel_id'] = $channel->id; // Use the channel ID that was just updated
        $post_data['channel_code'] = $request->channel_code;
        $post_data['other_details'] = json_encode($request->other_details);

        // Find the existing ChannelSetting entry and update it
        $channelSetting = ChannelSetting::where('channel_id', $channel->id)->first();
        if ($channelSetting) {
            // Handle file upload for brand_logo
            if ($request->hasFile('brand_logo')) {
                // Delete the old logo if it exists (optional)
                if ($channelSetting->brand_logo && file_exists(public_path($channelSetting->brand_logo))) {
                    unlink(public_path($channelSetting->brand_logo)); // Delete the old logo
                }

                $file = $request->file('brand_logo');
                $filename = 'brand_logo' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('assets/images/channels/logos'), $filename); // Save in public directory
                $post_data['brand_logo'] = $filename;
            } else {
                // Retain the old logo if no new file is uploaded
                $post_data['brand_logo'] = $channelSetting->brand_logo;
            }
            $channelSetting->update($post_data);
        }
        $paymentMappingData = $request->input('payment_mapping');
        if($paymentMappingData){
            foreach ($paymentMappingData as $paymentTypeId => $paymentMode) { 
                $paymentMapping = PaymentMapping::where('id', $paymentTypeId)
                ->where('company_id', $companyId)
                ->where('channel_id', $channel->id)
                ->first(); 
                if ($paymentMapping) {         
                    $paymentMapping->status = 1; 
                    $paymentMapping->payment_mode = $paymentMode; 
                    $paymentMapping->save();  
                }          
            }

        }
        $courierMappingData = $request->input('courier_mapping');
        if($courierMappingData){
            foreach ($courierMappingData as $courierTypeId => $courier_id) { 
                $courierMapping = CourierMapping::where('id', $courierTypeId)
                ->where('company_id', $companyId)
                ->where('channel_id', $channel->id)
                ->first(); 
                if ($courierMapping) {         
                    $courierMapping->status = 1; 
                    $courierMapping->courier_id = $courier_id;
                    $courierMapping->save();  
                }          
            }

        }
        
        // Redirect to the channels list page with a success message
        return redirect()->route('channels_list')->with('success', 'Shopify setting updated successfully.');
    }
    protected function registerWebhooks($shop, $accessToken, $companyId, $channelId)
    {
        // $topics = [
        //     'APP_UNINSTALLED',
        //     'ORDERS_CREATE',
        //     'ORDERS_UPDATED',
        //     'ORDERS_CANCELLED',
        //     'APP_SUBSCRIPTIONS_UPDATE'
        // ];
        $topics = [
            'APP_UNINSTALLED',
            'APP_SUBSCRIPTIONS_UPDATE'
        ];

        foreach ($topics as $topic) {
            // Create a dynamic callback URL including company_id and channel_id
            $callbackUrl = route('shopify.webhook', [
                'topic' => strtolower(str_replace('_', '', $topic)),
                'company_id' => $companyId,
                'channel_id' => $channelId,
            ]);

            // Register the webhook with Shopify using your service
            $this->shopifyGraphQLService->createWebhook($shop, $accessToken, $topic, $callbackUrl);
        }
    }
}
