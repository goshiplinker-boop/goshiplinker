<?php

namespace App\Http\Controllers\Seller\Couriers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CourierSetting;
use App\Models\Courier;

class DtdcLtlController extends Controller
{
   
    public function create()
    {
        return view('seller.couriers.settings.dtdc_ltl.create');
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
                'api_key' => 'required|string|max:100',
                'customer_code' => 'required|string|max:15',
                'api_token' => 'required|string',
                'pay_basis' => 'required|string|max:10',
                'status' => 'required|boolean',            
                'env_type' => 'required|string|in:dev,live',
            ]);
        $courier=Courier::create([
            'company_id'=> $companyId,
            'parent_id'=>'11',
            'name'=>$validatedData['courier_title'],
            'status'=> $validatedData['status'],
            'image_url'=> 'images/couriers/dtdc_ltl.png',
        ]);
        $post_data['courier_id'] = $courier->id;
        CourierSetting::create($post_data);
        // Redirect with a success message
        return redirect()->route(panelPrefix().'.couriers_list')->with('success', 'courier setting created successfully.');
    }

    public function edit(string $id)
    {
        // Find the CourierSetting by courier_id
        $companyId = session('company_id');
        try {
            $dtdc_ltl = CourierSetting::where(['courier_id' => $id, 'company_id' => $companyId])->firstOrFail();
            $courier_details = json_decode($dtdc_ltl->courier_details,true);
            $fields = [
                'shipment_mode',
                'api_key',
                'customer_code',
                'api_token',
                'pay_basis'
            ];
            foreach ($fields as $field) {
                $dtdc_ltl->{$field} = $courier_details[$field] ?? '';
            }
            
            return view('seller.couriers.settings.dtdc_ltl.edit', compact('dtdc_ltl'));
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
            'api_key' => 'required|string|max:100',
            'customer_code' => 'required|string|max:15',
            'api_token' => 'required|string',
            'pay_basis' => 'required|string|max:10',
            'status' => 'required|boolean',            
            'env_type' => 'required|string|in:dev,live',
        ]);

        // Find the CourierSetting by courier_id
        try {
            $dtdc_ltl = CourierSetting::where(['courier_id'=>$id,'company_id'=>$companyId])->firstOrFail();
            unset($post_data['courier_title']);
            // Update the CourierSetting
            $dtdc_ltl->update($post_data);

            // Update the associated Courier
            $courier = Courier::where(['id'=>$dtdc_ltl->courier_id,'company_id'=>$companyId])->firstOrFail();
            $courier->update([
               // 'name' => $validatedData['courier_title'],
                'status' => $validatedData['status'],
            ]);
            return redirect()->route(panelPrefix().'.couriers_list')->with('success', $validatedData['courier_title'].' settings updated successfully.');
        }catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where no record was found
            return redirect()->route(panelPrefix().'.couriers_list')->with('error', 'courier does not exist');
        }
    }
}
