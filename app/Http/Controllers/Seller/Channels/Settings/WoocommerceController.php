<?php

namespace App\Http\Controllers\Seller\Channels\Settings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChannelSetting;
use App\Models\Channel;
use App\Models\PaymentMapping;
use Illuminate\Support\Facades\Validator;
class WoocommerceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("seller.channels.settings.woocommerce.create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $companyId = $request->company_id ?? session("company_id", 0);

        try {
            // Validate request data
            $validatedData = $request->validate([
                "channel_title" => "required|string|max:50",
                "client_id" => "required|string|max:255",
                "secret_key" =>  "required|string|unique:channel_settings,secret_key",
                "brand_logo" => "nullable|image|mimes:jpeg,png,jpg,gif|max:2048",
                "brand_name" => "required|string|max:20",
                "channel_url" => "required|string|unique:channel_settings,channel_url",
                "other_details" => "required|array",
                "other_details.email" => "nullable|email",
                "other_details.order_tags" => "nullable|string",
                "other_details.gstin" => "nullable|string",
                "other_details.is_address_same" => "required|string",
                "other_details.fetch_status" => "required|string",
                "status" => "required|boolean",
            ]);

            // Handle file upload
            if ($request->hasFile("brand_logo")) {
                $file = $request->file("brand_logo");
                $filename = "brand_logo_" . time() . "." . $file->getClientOriginalExtension();
                $file->move(public_path("assets/images/channels/logos"), $filename);
                $validatedData["brand_logo"] = $filename;
            }
            DB::beginTransaction();
            try {
                // Create the Channel entry
                $channel = Channel::create([
                    "company_id" => $companyId,
                    "parent_id" => "3",
                    "name" => $validatedData["channel_title"],
                    "status" => $validatedData["status"],
                    "image_url" => "images/channels/woocommerce.png",
                ]);
            
                // Prepare ChannelSetting data
                $post_data = $validatedData;
                $post_data["company_id"] = $companyId;
                $post_data["channel_id"] = $channel->id;
                $post_data["channel_code"] = 'woocommerce';
                $post_data["other_details"] = json_encode($request->other_details);

                // Create ChannelSetting entry
                $dd = ChannelSetting::create($post_data);
                DB::commit();
                //return $dd;
                if ($request->expectsJson()) {
                    return response()->json([
                        "success" => true,
                        "result" => $post_data,
                        "message" => "WooCommerce setting created successfully.",
                    ]);
                }

                return redirect()->route("channels_list")->with("success", "WooCommerce setting created successfully.");
            }catch(\Exception $e){
                DB::rollBack();
                if ($request->expectsJson()) {
                    return response()->json([
                        "success" => false,
                        "message" => "An unexpected error occurred",
                        "errors" => $e->getMessage(), // Show exact error for debugging
                    ], 500);
                }
    
                return redirect()->back()->with("error", "An unexpected error occurred: " . $e->getMessage());

            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    "success" => false,
                    "errors" => $e->validator->errors(),
                ], 422);
            }
            return redirect()
                ->route("woocommerce.create")
                ->withErrors($e->validator->errors())
                ->withInput();
        } catch (\Exception $e) {
            // **Log the actual error for debugging**
            \Log::error("Channel Store Error: " . $e->getMessage(), ["trace" => $e->getTraceAsString()]);

            if ($request->expectsJson()) {
                return response()->json([
                    "success" => false,
                    "message" => "An unexpected error occurred",
                    "errors" => $e->getMessage(), // Show exact error for debugging
                ], 500);
            }

            return redirect()->back()->with("error", "An unexpected error occurred: " . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $companyId = session("company_id");
        $woocommerce = ChannelSetting::where([
            "channel_id" => $id,
            "company_id" => $companyId,
        ])->firstOrFail();
        $woocommerce->other_details = json_decode(
            $woocommerce->other_details,
            true
        );
        $payment_types = PaymentMapping::where("company_id", $companyId)
            ->where("channel_id", $id)
            ->get();
        return view("seller.channels.settings.woocommerce.edit", [
            "woocommerce" => $woocommerce,
            "payment_types" => $payment_types,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Retrieve the company ID from session
        $companyId = session("company_id");

        // Validate the request data
        $validatedData = $request->validate([
            "channel_title" => "required|string|max:50",
            "client_id" => "required|string|max:255",
            "secret_key" =>"required|string|unique:channel_settings,secret_key," . $id . ",channel_id",
            "brand_logo" => "nullable|image|mimes:jpeg,png,jpg,gif|max:2048",
            "brand_name" => "required|string|max:20",
            "channel_url" => "required|string|unique:channel_settings,channel_url," . $id . ",channel_id",
            "other_details" => "required|array",
            "other_details.email" => "nullable|email",
            "other_details.order_tags" => "nullable|string",
            "other_details.gstin" => "nullable|string",
            "other_details.is_address_same" => "required|numeric",
            "other_details.fetch_status" => "required|string",
            'other_details.auto_sync' => 'nullable|boolean',
            "status" => "required|boolean",
        ]);

        // Find the existing channel entry
        $channel = Channel::findOrFail($id);
        // Update the channel entry
        $channel->update([
            "name" => $validatedData["channel_title"],
            "status" => $validatedData["status"],
        ]);

        // Prepare post data
        $post_data = $validatedData;
        $post_data["company_id"] = $companyId;
        $post_data["channel_id"] = $channel->id; // Use the channel ID that was just updated
        $post_data["channel_code"] = $request->channel_code;
        $post_data["other_details"] = json_encode($request->other_details);

        // Find the existing ChannelSetting entry and update it
        $channelSetting = ChannelSetting::where(
            "channel_id",
            $channel->id
        )->first();
        if ($channelSetting) {
            // Handle file upload for brand_logo
            if ($request->hasFile("brand_logo")) {
                // Delete the old logo if it exists (optional)
                if (
                    $channelSetting->brand_logo &&
                    file_exists(public_path($channelSetting->brand_logo))
                ) {
                    unlink(public_path($channelSetting->brand_logo)); // Delete the old logo
                }

                $file = $request->file("brand_logo");
                $filename =
                    "brand_logo" .
                    time() .
                    "." .
                    $file->getClientOriginalExtension();
                $file->move(
                    public_path("assets/images/channels/logos"),
                    $filename
                ); // Save in public directory
                $post_data["brand_logo"] = $filename;
            } else {
                // Retain the old logo if no new file is uploaded
                $post_data["brand_logo"] = $channelSetting->brand_logo;
            }
            $channelSetting->update($post_data);
        }
        $paymentMappingData = $request->input("payment_mapping");
        if ($paymentMappingData) {
            foreach ($paymentMappingData as $paymentTypeId => $paymentMode) {
                $paymentMapping = PaymentMapping::where("id", $paymentTypeId)
                    ->where("company_id", $companyId)
                    ->where("channel_id", $channel->id)
                    ->first();
                if ($paymentMapping) {
                    $paymentMapping->status = 1;
                    $paymentMapping->payment_mode = $paymentMode;
                    $paymentMapping->save();
                }
            }
        }

        // Redirect to the channels list page with a success message
        return redirect()
            ->route("channels_list")
            ->with("success", "WooCommerce setting updated successfully.");
    }
    public function begin(Request $request)
    {
        // Get the shop and other query parameters from the request
        $store_url = $request->query("channel_url")??'';
        $endpoint = "/wc-auth/v1/authorize";
        $params = [
            "app_name" => "parcelMind",
            "scope" => "read_write",
            "user_id" => rand(1000, 9999),
            "return_url" => route("channels_list"),
            "callback_url" => route("woocommerce.callback"),
        ];
        $query_string = http_build_query($params);

        $install_url = $store_url . $endpoint . "?" . $query_string;

        // Redirect to Woocommerce install/approval URL
        return redirect($install_url);
    }

    public function callback(Request $request)
    {
        $params = $request->all();

        // if (!$request->has('key_id') || !$request->has('consumer_secret')) {
        //     return response()->json(['error' => 'Invalid response from WooCommerce'], 400);
        // }
    
        // $keyId = $request->query('key_id');
        // $secret = $request->query('consumer_secret'); // This is missing in your case
        // $userId = $request->query('user_id');
    
    
        // $companyId = session('company_id');
        // $channel = Channel::create([
        //     "company_id" => $companyId,
        //     "parent_id" => "3", // Assuming the parent ID is static
        //     "name" => 'WooCommerce',
        //     "status" => 1,
        //     "image_url" => "images/channels/woocommerce.png", // Set default image
        // ]);

        // // Prepare post data
        // $post_data = array();
        // $post_data["client_id"] = $keyId,
        // $post_data["secret_key"] = $secret,
        // $post_data["user_id"] = $userId,
        // $post_data["company_id"] = $companyId;
        // $post_data["channel_id"] = $channel->id; // Use the channel ID that was just created
        // $post_data["channel_code"] = 'woocommerce';
        // $post_data["other_details"] = json_encode($post_data);
        // // Create the ChannelSetting entry
        // ChannelSetting::create($post_data);
        // return redirect()
        //     ->route("channels_list")
        //     ->with("success", "WooCommerce setting install successfully.");
    }
    public function updateChannel(Request $request)
    {
        // Retrieve company ID and channel ID
        $companyId = $request->company_id ?? 0;
        $channel_id = $request->channel_id ?? 0;
    //return $request->all();
        // Validate the request data
        $validator = Validator::make($request->all(), [
            "channel_id" => "required|exists:channel_settings,channel_id",
            "company_id" => "required|exists:companies,id",
            "client_id" => "required|string|max:255",
            "secret_key" => "required|string|unique:channel_settings,secret_key,{$channel_id},channel_id"
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "errors" => $validator->errors(),
                "message" => "Validation failed."
            ], 422);
        }
    
        // Find the existing channel entry
        $channel = Channel::find($channel_id);
        if (!$channel) {
            return response()->json([
                "success" => false,
                "message" => "Channel not found."
            ], 404);
        }
    
        // Prepare post data
        $post_data = $validator->validated();
    
        // Find the existing ChannelSetting entry or create a new one
        $channelSetting = ChannelSetting::updateOrCreate(
            ["channel_id" => $channel->id],
            $post_data
        );
    
        return response()->json([
            "success" => true,
            "result" => [
                "channel_id" => $channelSetting->channel_id,
                "channel_title" => $channelSetting->channel_title,
                "company_id" => $channelSetting->company_id
            ],
            "message" => "WooCommerce setting updated successfully."
        ]);
    }
}