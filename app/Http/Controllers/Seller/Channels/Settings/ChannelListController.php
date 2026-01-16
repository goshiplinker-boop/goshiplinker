<?php

namespace App\Http\Controllers\Seller\Channels\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChannelSetting;
use Illuminate\Support\Facades\Auth;

use App\Models\Channel;
class ChannelListController extends Controller
{
    public function channelList(Request $request)
    {
        $defaultChannels = Channel::where(["status" => true, "company_id" => 0])
            ->whereNull("parent_id")
            ->get();
        // Retrieve the company ID from the session
        $companyId = $request->company_id ?? session("company_id", 0);
        $channelId = $request->channel_id ?? 0;
        $channel_url = $request->channel_url ?? '';
        // Check if the company ID exists in the session
        if(empty($channel_url)){
            $err = array();
            if(!$companyId){
                $err['company_id'] = 'company id not found';
                return redirect()
                        ->route("loginForm")
                        ->with("error", "company id not found.");
                

            }
            if(!$channelId){
                $err['channel_id'] = 'channel_id id not found';
            }           
            if ($request->expectsJson()) {                
                return response()->json([
                    "success" => false,
                    "errors" => $err,
                    "message" => "Invalid reuest details",
                ]);
            }
        }        

        // Fetch carriers associated with the company ID
        $companyChannels = Channel::join(
            "channel_settings",
            "channels.id",
            "=",
            "channel_settings.channel_id"
        )
        ->where("channel_settings.company_id", $companyId);
        if($channel_url){
            $companyChannels=$companyChannels->orWhere('channel_url',$channel_url);

        }    
        if (!empty($channelId)) {
            $companyChannels->where("channel_settings.channel_id", $channelId);
        }
        
        $companyChannels = $companyChannels->select(
                "channels.id",
                "channels.image_url",
                "channel_settings.channel_title",
                "channel_settings.channel_url",
                "channel_settings.brand_name",
                "channel_settings.client_id",
                "channel_settings.secret_key",
                "channel_settings.channel_code",
                "channel_settings.brand_logo",
                "channel_settings.status"
            )
            ->get();
        if ($request->expectsJson()) {
            return response()->json([
                "success" => true,
                "result" => $companyChannels,
                "message" => "Channels details found successfully.",
            ]);
        }
        return view("seller.channels.settings.channel_list", [
            "defaultChannels" => $defaultChannels,
            "companyChannels" => $companyChannels,
        ]);
    }
    public function apiGetChannels(Request $request)
    {
        $user = Auth::user();
        $companyId = $request->company_id ?? $user->company_id;
        $channelId = $request->channel_id ?? null;  
        $channel_url = $request->channel_url ?? '';  
    
        // Build query
        $query = Channel::join(
            "channel_settings",
            "channels.id",
            "=",
            "channel_settings.channel_id"
        )
        ->where("channel_settings.company_id", $companyId);
    
        if ($channel_url) {
            $query->where('channel_settings.channel_url', 'like', "%{$channel_url}%");
        }
    
        if (!empty($channelId)) {
            $query->where("channel_settings.channel_id", $channelId);
        }
    
        // Execute query
        $companyChannels = $query->select(
                "channels.id",
                "channels.image_url",
                "channel_settings.channel_title",
                "channel_settings.channel_url",
                "channel_settings.brand_name",
                "channel_settings.client_id",
                "channel_settings.secret_key",
                "channel_settings.channel_code",
                "channel_settings.brand_logo",
                "channel_settings.status"
            )
            ->get();
    
        // Handle empty results
        if ($companyChannels->isEmpty()) {
            return response()->json([
                "success" => false,
                "message" => "No channels found for this company.",
            ], 404); 
        }
    
        // Success response
        return response()->json([
            "success" => true,
            "result" => $companyChannels,
            "message" => $channelId ? "Channel details found successfully." : "All integrated channels details found successfully.",
        ], 200); 
    }
    
}