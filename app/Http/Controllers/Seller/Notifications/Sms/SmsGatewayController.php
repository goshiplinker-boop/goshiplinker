<?php

namespace App\Http\Controllers\Seller\Notifications\Sms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SmsGateway;
use App\Models\SmsDltSetting;
use App\Models\Company;

class SmsGatewayController extends Controller
{
    public function index()
    {
        $company_id = session('company_id');
        $sms_gateways = SmsGateway::where('company_id',$company_id)->get();
        return view('seller.notifications.sms.gateways.index', compact('sms_gateways'));
    }

    public function create()
    {    $company_id = session('company_id');
        $companies = Company::all();
        $settings = SmsDltSetting::where('company_id', $company_id)->get();
        return view('seller.notifications.sms.gateways.create', compact('companies','settings'));
    }

    public function store(Request $request)
    {
        $company_id = session('company_id');
        $validated = $request->validate([
            'gateway_name' => 'required|string|max:255',
            'http_method' => 'required|in:GET,POST',
            'gateway_url' => 'required|url',
            'dlt_header_name' => 'nullable|string|max:255',
            'dlt_header_id' => 'nullable|string|max:255',
            'dlt_template_name' => 'nullable|string|max:255',
            'dlt_template_id' => 'nullable|string|max:255',
            'mobile' => 'required|string|max:20',
            'other_parameters' => 'nullable|array',
            'status' => 'required|boolean',
        ]);
        $validated['company_id'] = $company_id;
        $validated['other_parameters'] = ($request->other_parameters)?json_encode($request->other_parameters):null;
       
        SmsGateway::where('company_id', $company_id)->update(['status' => 0]);
        $validated['status'] = 1;
        $newGateway = SmsGateway::create($validated);
       
        return redirect()->route('gateway_list')->with('success', 'SMS Gateway created successfully.');
    }

    public function edit(Request $request)
    {
        $gateway_id = $request->gateway_id??0;  
        $company_id = session('company_id'); 
        $settings = SmsDltSetting::where('company_id', $company_id)->get();
        $sms_gateway = SmsGateway::where('company_id',$company_id)->where('id',$gateway_id)->first();
        $sms_gateway->other_parameters = !empty($sms_gateway->other_parameters)?json_decode($sms_gateway->other_parameters, true):array();
        return view('seller.notifications.sms.gateways.edit', compact('sms_gateway','settings'));
    }

    public function update(Request $request)
    {
        $company_id = session('company_id');
        $id = $request->query('gateway_id');
        $sms_gateway = SmsGateway::where('company_id',$company_id)->where('id',$id)->first();
        
        $validated = $request->validate([
            'gateway_name' => 'required|string|max:255',
            'http_method' => 'required|in:GET,POST',
            'gateway_url' => 'required|url',
            'dlt_header_name' => 'nullable|string|max:255',
            'dlt_header_id' => 'nullable|string|max:255',
            'dlt_template_name' => 'nullable|string|max:255',
            'dlt_template_id' => 'nullable|string|max:255',
            'mobile' => 'required|string|max:20',
            'other_parameters' => 'nullable|array',
            'status' => 'required|boolean',
        ]);
        if (isset($validated['other_parameters'])) {
            $validated['other_parameters'] = array_values($validated['other_parameters']);
            $validated['other_parameters'] = ($validated['other_parameters'])?json_encode($validated['other_parameters']):null;
        }
        if ($validated['status'] == 1) {
            SmsGateway::where('company_id', $company_id)
            ->where('id', '!=', $id)
            ->update(['status' => 0]);
        }
      
        $sms_gateway->update($validated);

        return redirect()->route('gateway_list')->with('success', 'SMS Gateway updated successfully.');
    }
}