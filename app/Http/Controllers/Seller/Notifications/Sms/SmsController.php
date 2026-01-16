<?php

namespace App\Http\Controllers\Seller\Notifications\Sms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SmsDltSetting;
use App\Models\SmsDltTemplate;
use App\Models\ShipmentStatus;
use App\Models\SmsGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
class SmsController extends Controller
{
    // List all settings
    public function index()
    {
        $company_id = session('company_id');
        $settings = SmsDltSetting::where('company_id',$company_id)->get();
        return view('seller.notifications.sms.index', compact('settings'));
    }
    
    // Store a new DLT Setting
    public function storeSetting(Request $request)
    {
        $company_id = session('company_id');
        $validated = $request->validate([
            'header_id' => 'required|string|max:15',
            'header_registration_id' => 'required|numeric',
            'telecom_provider_name' => 'required|string',
            'company_legal_name' => 'required|string',
            'status' => 'required|boolean',
        ]);
        $validated['company_id'] = $company_id;
        if ($request->id) {
            // Update existing
            $setting = SmsDltSetting::findOrFail($request->id);
            $setting->update($validated);
            return redirect()->back()->with('success', 'DLT Setting updated successfully!');
        } else {
            // Create new
            SmsDltSetting::create($validated);
            return redirect()->back()->with('success', 'DLT Setting created successfully!');
        }
    }

    // List all template
    public function sms_templates()
    {
        $company_id = session('company_id');
        $template_settings = SmsDltTemplate::where('company_id',$company_id)->get();
        return view('seller.notifications.sms.templates.sms_templates', compact('template_settings'));
    }
    // edit a specific DLT Template
    public function createTemplate()
    {   
        $shipment_statuses = ShipmentStatus::whereColumn('parent_code', 'code')
        ->where('status', '1')->get();
        return view('seller.notifications.sms.templates.create',compact('shipment_statuses'));
    }
    // Store a new DLT Template
    public function storeTemplate(Request $request)
    {
        $company_id = session('company_id');
        $validated = $request->validate([
            'template_registration_id' => 'required|numeric',
            'order_status' => 'required|string',
            'message_content' => 'required|string',
            'status' => 'required|boolean',
        ]);
        $validated['company_id'] = $company_id;
        $template = SmsDltTemplate::create($validated);
        return redirect()->route('sms_templates')->with('success', 'DLT Template created successfully');
    }   

    // edit a specific DLT Template
    public function editTemplate(Request $request)
    {
        $id = $request->id??0;
        $company_id = session('company_id');
        $template = SmsDltTemplate::with('company')->findOrFail($id);
        $shipment_statuses = ShipmentStatus::whereColumn('parent_code', 'code')
        ->where('status', '1')->get();
        return view('seller.notifications.sms.templates.edit', compact('template','shipment_statuses'));
    }

   
    // Update a DLT Template
    public function updateTemplate(Request $request)
    {
        $company_id = session('company_id');
        $id = $request->id??0;
        $template = SmsDltTemplate::findOrFail($id);
        $validated = $request->validate([
            'template_registration_id' => 'sometimes|required|numeric',
            'order_status' => 'sometimes|required|string',
            'message_content' => 'sometimes|required|string',
            'status' => 'sometimes|required|boolean',
        ]);
        $validated['company_id'] = $company_id;
        $template->update($validated);
        return redirect()->route('sms_templates')->with('success', 'DLT Template updated successfully');
    }
    
    public function Testsms(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "phone" => ["required", "regex:/^[0-9]{10}$/"],
        ], [
            "phone.regex" => "Phone number must be exactly 10 digits and contain only numbers.",
        ]);
    
        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->has("phone")) {
                return redirect()->back()->with("error", $errors->first("phone"));
            }
        }
    
        $phone = $request->input('phone');
        $templateId = $request->input('id');
        $company_id = session('company_id');
    
        $gateway = SmsGateway::where('status', 1)->where('company_id', $company_id)->first();
        $template = SmsDltTemplate::where('id', $templateId)
            ->where('company_id', $company_id)
            ->where('status', 1)
            ->first();
    
        if (!$gateway || !$template) {
            return redirect()->back()->with('error', 'Gateway or template not found');
        }
    
        if ($gateway) {
            $http_method = $gateway->http_method;
            $gateway_url = $gateway->gateway_url;
            $params = [];
    
            $params[$gateway->dlt_header_name] = $gateway->dlt_header_id;
            $params[$gateway->dlt_template_name]=$template->message_content;
            $params[$gateway->dlt_template_id]=$template->template_registration_id;
            $params[$gateway->mobile] = $phone;
    
            $name = "John";
            $orderid = "123456";
    
            $other_parameters = (!empty($gateway->other_parameters)) ? json_decode($gateway->other_parameters, true) : [];
    
            if (!empty($other_parameters)) {
                foreach ($other_parameters as $other_parameter) {
                    $params[$other_parameter['key']] = $other_parameter['value'];
                }
            }
    
            $url_parms = http_build_query($params);
            $sms = $template->message_content;
            $template_id = $template->template_registration_id;
            $finalMessage = str_replace(['{customer}', '{order_id}'], [$name, $orderid], $sms);
            $finalMessage = rawurlencode($finalMessage);
            $finalUrl = str_replace(['message','gateway_template_id'], [$finalMessage,$template_id], $url_parms);
            $finalUrl = $gateway->gateway_url . '?' . $finalUrl;
    
            if (strtoupper($gateway->http_method) === 'GET') {
                try {
                    $response = Http::get($finalUrl);
                    $responseData = $response->json();
    
                    if (isset($responseData['STATUS']) && $responseData['STATUS'] === 'OK') {
                        return redirect()->back()->with('success', 'SMS sent successfully!');
                    } else {
                        $info = $responseData['RESPONSE']['INFO'] ?? 'Unknown error';
                        return redirect()->back()->with('error', 'SMS not sent. ' . $info);
                    }
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', 'An error occurred while sending the SMS.');
                }
            } else {
                return redirect()->back()->with('error', 'Data not found. Only GET requests are allowed.');
            }
        }
    }           

}


 
     

