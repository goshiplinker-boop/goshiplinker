<?php

namespace App\Http\Controllers\Seller\Channels\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ChannelSetting;
use App\Models\Channel;
use App\Models\User;
use App\Models\Company;
use App\Models\PaymentMapping;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use UnexpectedValueException;
class WixController extends Controller
{
    public function create()
    {
        return view("seller.channels.settings.wix.create");
    }

    public function handleInstall(Request $request)
    {
        // 1. Wix redirects here with a `token` after the user clicks "Add App".
        $token = $request->query('token');

        if (!$token) {
            Log::error('Wix installation initiated without a token.');
            return response('Installation token missing.', 400);
        }

        $wixAppId = config('services.wix.app_id');
        // This is the URL you configured as "Redirect URL" in Wix Dev Center
        $redirectUrl = config('services.wix.redirect_uri');

        // 2. Redirect the user to the Wix authorization URL.
        $permissionRequestUrl = "https://www.wix.com/installer/install";
        $queryParams = http_build_query([
            'token' => $token,
            'appId' => $wixAppId,
            'redirectUrl' => $redirectUrl,
        ]);
        return redirect()->away($permissionRequestUrl . '?' . $queryParams);
    }
    public function handleCallback(Request $request)
    {
        $authorizationCode = $request->query('code');
        $instanceId = $request->query('instanceId');
    
        if (!$authorizationCode || !$instanceId) {
            return response('Authorization code or instanceId missing.', 400);
        }
    
        $wixAppId = config('services.wix.app_id');
        $wixAppSecret = config('services.wix.secret');
        $tokenUrl = 'https://www.wixapis.com/oauth/access';
    
        try {
            $response = Http::post($tokenUrl, [
                'grant_type' => 'authorization_code',
                'client_id' => $wixAppId,
                'client_secret' => $wixAppSecret,
                'code' => $authorizationCode,
            ]);
    
            if ($response->failed()) {
                return response('Failed to obtain access token: ' . $response->body(), $response->status());
            }
    
            $tokenData = $response->json();
            $accessToken = $tokenData['access_token'];
            $refreshToken = $tokenData['refresh_token']; // Store securely if needed
            try{
            // Wrap DB operations in transaction
                DB::transaction(function () use ($instanceId, &$companyId, &$channelSetting,&$refreshToken) {
                    $channelSetting = ChannelSetting::where('client_id', $instanceId)->first();
        
                    $companyId = Auth::check() ? session('company_id') : 0;
        
                    if (empty($companyId) && $channelSetting && $channelSetting->company_id) {
                        $companyId = $channelSetting->company_id;
                        $user = User::where('company_id', $companyId)->first();
        
                        if ($user) {
                            Auth::login($user);
                            Session::put('role_id', $user->role_id);
                            Session::put('company_id', $companyId);
                        }
                    }
        
                    if (empty($companyId)) {
                        $email = $instanceId . '@gmail.com';
        
                        if (Company::where('company_email_id', $email)->exists()) {
                            $email = time() . $email;
                        }
        
                        $company = Company::create([
                            'legal_registered_name' => '',
                            'phone_number' => '1234567890',
                            'company_email_id' => $email,
                            'country_code' => 'IN',
                        ]);
                        $companyId = $company->id;
        
                        $user = User::create([
                            'name' => "Wix_{$instanceId}",
                            'email' => $email,
                            'password' => bcrypt($instanceId),
                            'company_id' => $companyId,
                            'role_id' => 2,
                        ]);
        
                        Auth::login($user);
                        Session::put('role_id', $user->role_id);
                        Session::put('company_id', $companyId);
                    }
        
                    if ($channelSetting && $channelSetting->company_id != $companyId) {                    
                        // Throw an exception to rollback the transaction and stop processing
                        throw new \Exception("Wix app instance $instanceId is already installed in another vendor account.");
                    }
        
                    if ($channelSetting) {
                        $channelId = $channelSetting->channel_id;
                    } else {
                        $channel = Channel::create([
                            'company_id' => $companyId,
                            'parent_id' => 6,
                            'name' => 'Wix',
                            'status' => 1,
                            'image_url' => 'images/channels/wix.png',
                        ]);
                        $channelId = $channel->id;
                    }
        
                    $channelSetting = ChannelSetting::updateOrCreate(
                        ['client_id' => $instanceId],
                        [
                            'channel_title' => 'Wix Store',
                            'client_id' => $instanceId,
                            'brand_name' => 'Wix',
                            'secret_key' => $refreshToken,
                            'company_id' => $companyId,
                            'channel_id' => $channelId,
                            'channel_code' => 'wix',
                            'status' => 1,
                        ]
                    );
                });
            } catch (\Exception $e) {                
                return redirect()->route('channels_list')->withErrors($e->getMessage());
            }
            $channelId = $channelSetting->channel_id??0;
            // Call your WixOAuthService or similar to store tokens if needed
            $accessToken = app(\App\Services\WixOAuthService::class)->createAccessToken($channelSetting);
            if($accessToken){
                $sitedetails =  $this->getSiteDetails($accessToken);
                if($sitedetails){
                    $properties = $sitedetails['properties']??[];
                    if($properties){
                        $country_code = $properties['locale']['country']??'';
                        $email = $properties['email']??'';
                        $phone = $properties['phone']??'';
                        $phone = preg_replace('/\D/', '', $phone);
                        $phone = substr($phone, -10);
                        $siteDisplayName = $properties['siteDisplayName']??'';
                        $company_name = $properties['businessName']??'';
                        $address = $properties['address']??[];
                        $street = $address['street']??'';
                        $city = $address['city']??'';
                        $country = $address['country']??'IN';
                        $state = $address['state']??'';
                        $zip = $address['zip']??'';
                        $googleFormattedAddress = $address['googleFormattedAddress']??'';
                        $streetNumber = $address['streetNumber']??'';
                        $apartmentNumber = $address['apartmentNumber']??'';
                        $complete_address =trim($street.' '.$streetNumber.' '.$streetNumber.' '.$apartmentNumber.' '.$googleFormattedAddress);
                        $userdata=[];
                        if($company_name){
                            $userdata['name'] = $company_name;
                        }                        
                        if($email){
                            $userdata['email'] = $email;
                        }
                        if($userdata){
                            User::where('company_id', $companyId)->update($userdata);
                        }  
                        $company = Company::find($companyId);
                        if ($company && is_object($company)) {
                            $companydetails = [];

                            if (empty($company->legal_registered_name) && !empty($company_name)) {
                                $companydetails['legal_registered_name'] = $company_name;
                            }
                            if (empty($company->company_email_id) && !empty($email)) {
                                $companydetails['company_email_id'] = $email;
                            }
                            if (empty($company->phone_number) || !empty($phone)) {
                                $companydetails['phone_number'] = $phone;
                            }
                            if (empty($company->brand_name) && !empty($siteDisplayName)) {
                                $companydetails['brand_name'] = $siteDisplayName;
                            }
                            if (empty($company->address) && !empty($complete_address)) {
                                $companydetails['address'] = trim($complete_address);
                            }
                            if (empty($company->pincode) && !empty($zip)) {
                                $companydetails['pincode'] = $zip;
                            }
                            if (empty($company->city) && !empty($city)) {
                                $companydetails['city'] = $city;
                            }
                            if (empty($company->state_code) && !empty($state)) {
                                $companydetails['state_code'] = $state;
                            }
                            if (empty($company->country_code) && !empty($country)) {
                                $companydetails['country_code'] = $country;
                            }

                            if (!empty($companydetails)) {
                                $company->update($companydetails);
                            }
                        }                       

                        if ($siteDisplayName) {
                            // Update brand name in channel settings
                            ChannelSetting::where('company_id', $companyId)
                                ->where('client_id', $instanceId)
                                ->update(['brand_name' => $siteDisplayName]);

                            // Update name in channel
                            Channel::where('company_id', $companyId)
                                ->where('id', $channelId)
                                ->update(['name' => $siteDisplayName]);
                        }                       
                            
                        
                    }                   

                }

            }       
    
            return redirect()->route('channels_list')->with('success', 'Wix setting created successfully.');
    
        } catch (\Exception $e) {
            return redirect()->route('channels_list')->withErrors('Error: ' . $e->getMessage());
        }
    }

  
    public function edit(string $id)
    {   
        $companyId = session('company_id');
        $wix = ChannelSetting::where(['channel_id'=>$id,'company_id'=>$companyId])->firstOrFail();
        $wix->other_details = ($wix->other_details)?json_decode($wix->other_details,true):array();
        $payment_types = PaymentMapping::where('company_id', $companyId)
        ->where('channel_id', $id)
        ->get();
        return view('seller.channels.settings.wix.edit',['wix'=>$wix,'payment_types'=>$payment_types]); 
    }
   
    public function update(Request $request, $id)
    {
        // Retrieve the company ID from session
        $companyId = session('company_id');        
        // Validate the request data
        $validatedData = $request->validate([
            'channel_title' => 'required|string|max:50',
            'brand_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'brand_name' => 'required|string|max:20',
            'channel_url' => 'required|string',
            'other_details' => 'required|array',
            'other_details.is_address_same' => 'required|numeric',
            'other_details.pull_update_orders' => 'required|numeric',
            'other_details.fetch_status' => 'required|string|in:NOT_FULFILLED',
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
        

        // Find the existing ChannelSetting entry and update it
        $channelSetting = ChannelSetting::where('channel_id', $channel->id)->first();
        if ($channelSetting) {
            $other_details = json_decode($channelSetting->other_details,true);
            $other_details['is_address_same'] = $request->other_details['is_address_same'];
            $other_details['pull_update_orders'] = $request->other_details['pull_update_orders'];
            $other_details['fetch_status'] = $request->other_details['fetch_status'];
            $other_details['auto_sync'] = $request->other_details['auto_sync'];
            $post_data['other_details'] = json_encode($other_details);
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
        
        // Redirect to the channels list page with a success message
        return redirect()->route('channels_list')->with('success', 'Wix setting updated successfully.');
    }
    public function app(Request $request)
    {
        $instance = $request->query('instance', '');

        // Step 1: Check if instance exists
        if (empty($instance)) {
            return redirect()->route('welcome')->with('error', 'Access denied: Missing instance parameter.');
        }

        // Step 2: Validate instance format
        $parts = explode('.', $instance);
        if (count($parts) < 2) {
            return redirect()->route('welcome')->with('error', 'Access denied: Invalid instance parameter.');
        }

        // Step 3: Decode and validate instance data
        $decodedData = json_decode(base64_decode($parts[1]), true);
        if (!$decodedData) {
            return redirect()->route('welcome')->with('error', 'Access denied: Invalid instance parameter.');
        }

        $wixAppId    = config('services.wix.app_id');
        $instanceId  = $decodedData['instanceId'] ?? '';
        $appDefId    = $decodedData['appDefId'] ?? '';

        if ($wixAppId !== $appDefId) {
            return redirect()->route('welcome')->with('error', 'Access denied: Invalid app ID.');
        }

        // Step 4: Get channel setting and determine company ID
        $channelSetting = ChannelSetting::where('client_id', $instanceId)->first();
        $companyId = Auth::check() ? session('company_id') : 0;

        if (empty($companyId) && $channelSetting && $channelSetting->company_id) {
            $companyId = $channelSetting->company_id;
            $user = User::where('company_id', $companyId)->first();

            if ($user) {
                Auth::login($user);
                Session::put('role_id', $user->role_id);
                Session::put('company_id', $companyId);

                return redirect()->route('channels_list');
            }

            return redirect()->route('welcome')->with('error', 'Invalid user.');
        }

        // Step 5: Check for cross-company access
        if ($companyId && $channelSetting && $companyId != $channelSetting->company_id) {
            return redirect()->route('welcome')->with('error', 'This app exists in another Parcelmind client account.');
        }

        // Step 6: Redirect to channel list
        return redirect()->route('channels_list');
    }
    private function getSiteDetails($token){
        $response = Http::withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json',
        ])->get('https://www.wixapis.com/site-properties/v4/properties');

        if(!$response->successful()){
            return [];

        }else{
            return $response->json();
        }
    }
   
}
