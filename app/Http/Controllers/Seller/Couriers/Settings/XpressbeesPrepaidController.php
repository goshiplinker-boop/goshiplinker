<?php

namespace App\Http\Controllers\Seller\Couriers\Settings;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CourierSetting;
use App\Models\Courier;
use Illuminate\Support\Facades\Http;

class XpressbeesPrepaidController extends Controller
{
    public function create()
    {
        return view('seller.couriers.settings.xpressbees_prepaid.create');
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
            'xb_courier_id' => 'required',
            'username' => 'required|string|max:50',
            'password' => 'required|string|max:50',
            'status' => 'required|boolean',            
            'env_type' => 'required|string|in:dev,live',
        ]);
        $courier=Courier::create([
            'company_id'=> $companyId,
            'parent_id'=>'7',
            'name'=>$validatedData['courier_title'],
            'status'=> $validatedData['status'],
            'image_url'=> 'images/couriers/xpressbees_prepaid.png',
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
            $xpressbees_prepaid = CourierSetting::where(['courier_id' => $id, 'company_id' => $companyId])->firstOrFail();
            $courier_details = json_decode($xpressbees_prepaid->courier_details,true);            
            $fields = [
                'shipment_mode',
                'xb_courier_id',
                'username',
                'password',
            ];

            foreach ($fields as $field) {
                $xpressbees_prepaid->{$field} = $courier_details[$field] ?? '';
            }
            return view('seller.couriers.settings.xpressbees_prepaid.edit', compact('xpressbees_prepaid'));
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
            'xb_courier_id' => 'required',
            'username' => 'required|string|max:50',
            'password' => 'required|string|max:50',
            'status' => 'required|boolean',            
            'env_type' => 'required|string|in:dev,live',
        ]);
        // Find the CourierSetting by courier_id
        try {
            $xpressbees_prepaid = CourierSetting::where(['courier_id'=>$id,'company_id'=>$companyId])->firstOrFail();
            unset($post_data['courier_title']);
            // Update the CourierSetting
            $xpressbees_prepaid->update($post_data);

            // Update the associated Courier
            $courier = Courier::where(['id'=>$xpressbees_prepaid->courier_id,'company_id'=>$companyId])->firstOrFail();
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
}
