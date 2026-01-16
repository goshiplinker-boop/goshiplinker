<?php

namespace App\Http\Controllers\Seller\Channels\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChannelSetting;
use App\Models\Channel;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use App\Models\PaymentMapping;
class ShopbaseController extends Controller
{
    public function create()
    {
       return view('seller.channels.settings.shopbase.create');
    }
    public function store(Request $request)
    {
        // Retrieve the company ID from session
        $companyId = session("company_id");

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
            'other_details.fetch_status' => 'required|string',
            'status' => 'required|boolean',
        ]);

        // Handle file upload for brand_logo
        if ($request->hasFile("brand_logo")) {
            $file = $request->file("brand_logo");
            $filename =
                "brand_logo" .
                time() .
                "." .
                $file->getClientOriginalExtension();
            $file->move(public_path("assets/images/channels/logos"), $filename); // Save in public directory
            $validatedData["brand_logo"] = $filename;
        }
        // Create the channel entry
        $channel = Channel::create([
            "company_id" => $companyId,
            "parent_id" => "4", // Assuming the parent ID is static
            "name" => $validatedData["channel_title"],
            "status" => $validatedData["status"],
            "image_url" => "images/channels/shopbase.png", // Set default image
        ]);

        // Prepare post data
        $post_data = $validatedData;
        $post_data["company_id"] = $companyId;
        $post_data["channel_id"] = $channel->id; // Use the channel ID that was just created
        $post_data["channel_code"] = $request->channel_code;
        $post_data["other_details"] = json_encode($request->other_details);
        // Create the ChannelSetting entry
        ChannelSetting::create($post_data);

        // Redirect to the channels list page with a success message
        return redirect()
            ->route("channels_list")
            ->with("success", "Shopbase setting created successfully.");
    }
    public function edit(string $id)
    {   
        $companyId = session('company_id');
        $shopbase = ChannelSetting::where(['channel_id'=>$id,'company_id'=>$companyId])->firstOrFail();
        $shopbase->other_details = ($shopbase->other_details)?json_decode($shopbase->other_details,true):array();
        $payment_types = PaymentMapping::where('company_id', $companyId)
        ->where('channel_id', $id)
        ->get();
        return view('seller.channels.settings.shopbase.edit',['shopbase'=>$shopbase,'payment_types'=>$payment_types]); 
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
        
        // Redirect to the channels list page with a success message
        return redirect()->route('channels_list')->with('success', 'shopbase setting updated successfully.');
    }
}
