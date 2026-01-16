<?php

namespace App\Http\Controllers\Seller\Couriers\Settings;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CourierSetting;
use App\Models\Courier;
use Illuminate\Support\Facades\Http;

class XpressbeesPostpaidController extends Controller
{
    public function create()
    {
        return view('seller.couriers.settings.xpressbees_postpaid.create');
    }

   
    public function store(Request $request)
    {
        $companyId = session('company_id');
        $post_data = $request->all();
        $post_data['courier_details'] = json_encode($request->all());
        $post_data['company_id'] = $companyId;
        $validatedData = $request->validate([
            'courier_title' => 'required|string|max:50',
            'shipment_mode' => 'required',
            'service_type' => 'required',
            'xbkey' => 'required|string',
            'secret_key' => 'required|string',
            'version' => 'required|string',
            'business_account_name' => 'required|string',
            'username' => 'required|string|max:50',
            'password' => 'required|string|max:50',
            'status' => 'required|boolean',            
            'env_type' => 'required|string|in:dev,live',
        ]);
        $courier=Courier::create([
            'company_id'=> $companyId,
            'parent_id'=>'6',
            'name'=>$validatedData['courier_title'],
            'status'=> $validatedData['status'],
            'image_url'=> 'images/couriers/xpressbees_postpaid.png',
        ]);
        $post_data['courier_id'] = $courier->id;
        CourierSetting::create($post_data);
        return redirect()->route(panelPrefix().'.couriers_list')->with('success', 'courier setting created successfully.');
    }

    public function edit(string $id)
    {
        // Find the CourierSetting by courier_id
        $companyId = session('company_id');
        try {
            $xpressbees_postpaid = CourierSetting::where(['courier_id' => $id, 'company_id' => $companyId])->firstOrFail();
            $courier_details = json_decode($xpressbees_postpaid->courier_details,true);            
            $fields = [
                'shipment_mode',
                'service_type',
                'username',
                'password',
                'secret_key',
                'business_account_name',
                'xbkey',
                'version'
            ];

            foreach ($fields as $field) {
                $xpressbees_postpaid->{$field} = $courier_details[$field] ?? '';
            }
            return view('seller.couriers.settings.xpressbees_postpaid.edit', compact('xpressbees_postpaid'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where no record was found
            return redirect()->route(panelPrefix().'.couriers_list')->with('error', 'courier does not exist');
        }

    }

    public function update(Request $request, string $id)
    {
        $companyId = session('company_id');

        $post_data = $request->all();
        $post_data['courier_details'] = json_encode($request->all());
        $post_data['company_id'] = $companyId;
        // Validate incoming data
        $validatedData = $request->validate([
            'courier_title' => 'required|string|max:50',
            'shipment_mode' => 'required',
            'service_type' => 'required',
            'xbkey' => 'required|string',
            'secret_key' => 'required|string',
            'version' => 'required|string',
            'business_account_name' => 'required|string',
            'username' => 'required|string|max:50',
            'password' => 'required|string|max:50',
            'status' => 'required|boolean',            
            'env_type' => 'required|string|in:dev,live',
        ]);

        // Find the CourierSetting by courier_id
        try {
            $xpressbees_postpaid = CourierSetting::where(['courier_id'=>$id,'company_id'=>$companyId])->firstOrFail();
            unset($post_data['courier_title']);
            // Update the CourierSetting
            $xpressbees_postpaid->update($post_data);

            // Update the associated Courier
            $courier = Courier::where(['id'=>$xpressbees_postpaid->courier_id,'company_id'=>$companyId])->firstOrFail();
            $courier->update([
                //'name' => $validatedData['courier_title'],
                'status' => $validatedData['status'],
            ]);
            return redirect()->route(panelPrefix().'.couriers_list')->with('success', $validatedData['courier_title'].' settings updated successfully.');
        }catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where no record was found
            return redirect()->route(panelPrefix().'.couriers_list')->with('error', 'courier does not exist');
        }
    }
    public function fetch_awb($request,$courier_settings){
        $courier_details = ($courier_settings->courier_details)?json_decode($courier_settings['courier_details'],true):array();
        $xbkey = $courier_details['xbkey'];
        $env_type = $courier_settings['env_type']??'dev';
        $json = array();
        // Validate key presence
        if (!$xbkey) {
            session()->flash('error', 'Xpress Bees says: Unauthorised Request');
            return false;
        }

        // Prepare request parameters
        $requestParams = [
            'DeliveryType' => ($request->payment_type === 'C') ? 'COD' : 'PREPAID',
            'BusinessUnit' => 'ECOM',
            'ServiceType' => 'FORWARD'
        ];

        $postData = json_encode($requestParams);

        // Determine URLs
        if ($env_type === 'dev') {
            $url = 'http://114.143.206.69:803/StandardForwardStagingService.svc/AWBNumberSeriesGeneration';
            $url2 = 'http://114.143.206.69:803/StandardForwardStagingService.svc/GetAWBNumberGeneratedSeries';
        } else {
            $url = "https://xbclientapi.xbees.in/POSTShipmentService.svc/AWBNumberSeriesGeneration";
            $url2 = 'https://xbclientapi.xbees.in/TrackingService.svc/GetAWBNumberGeneratedSeries';
        }

        $response = Http::timeout(60)
            ->withHeaders([
                'cache-control' => 'no-cache',
                'content-type' => 'application/json',
                'XBKey' => $xbkey
            ])
            ->post($url, $requestParams);
        $result = $response->json();
        if ($result['ReturnCode'] == '100') {
            $batch_id = $result['BatchID'];
            // If batch exists, request using batch ID
            $batchRequest = [
                'BusinessUnit' => 'ECOM',
                'ServiceType' => 'FORWARD',
                'BatchID' => $batch_id
            ];
            $response = Http::timeout(60)
            ->withHeaders([
                'cache-control' => 'no-cache',
                'content-type' => 'application/json',
                'XBKey' => $xbkey
            ])
            ->post($url2, $batchRequest);

            $result = $response->json();
            if ($result['ReturnCode'] == '100') {        
                $json['success'] = true;
                $json['awb'] = $result['AWBNoSeries'];
            } else {
                $json['error'] = $result['ReturnMessage']??'';
            }
        }else{
            $json['error'] = $result['ReturnMessage']??'';
        }
        return $json;
    }
}
