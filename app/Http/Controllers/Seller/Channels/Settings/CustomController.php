<?php

namespace App\Http\Controllers\Seller\Channels\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChannelSetting;
use App\Models\Channel;
class CustomController extends Controller
{
    public function create()
    { 
       return view('seller.channels.settings.custom.create');
    }

    public function store(Request $request)
    {
        // Retrieve the company ID from session
        $companyId = session('company_id');
        
        // Validate the request data
        $validatedData = $request->validate([
            'channel_title' => 'required|string|max:50',
            'brand_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'brand_name' => 'required|string|max:20',
            'status' => 'required|boolean'
        ]);
    
        // Handle file upload for brand_logo
        if ($request->hasFile('brand_logo')) {
            $file = $request->file('brand_logo');
            $filename = 'brand_logo' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/images/channels/logos'), $filename); // Save in public directory
            $validatedData['brand_logo'] = $filename;
        }
        // Create the channel entry
        $channel = Channel::create([
            'company_id' => $companyId,
            'parent_id' => '1',  // Assuming the parent ID is static
            'name' => $validatedData['channel_title'],
            'status' => $validatedData['status'],
            'image_url' => 'images/channels/custom.png', // Set default image
        ]);
    
        // Prepare post data
        $post_data = $validatedData;
        $post_data['company_id'] = $companyId;
        $post_data['channel_id'] = $channel->id; // Use the channel ID that was just created
        $post_data['channel_code'] = $request->channel_code;
        
        // Create the ChannelSetting entry
        ChannelSetting::create($post_data);
    
        // Redirect to the channels list page with a success message
        return redirect()->route('channels_list')->with('success', 'Custom Channel setting created successfully.');
    }
    public function edit(string $id)
    {   
        $companyId = session('company_id');
        $custom = ChannelSetting::where(['channel_id'=>$id,'company_id'=>$companyId])->firstOrFail();
        return view('seller.channels.settings.custom.edit',compact('custom')); 
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

        // Redirect to the channels list page with a success message
        return redirect()->route('channels_list')->with('success', 'custom channel setting updated successfully.');
    }
}
