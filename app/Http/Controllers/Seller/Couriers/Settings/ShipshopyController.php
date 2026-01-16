<?php

namespace App\Http\Controllers\Seller\Couriers\Settings;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CourierSetting;
use App\Models\Courier;
use Illuminate\Support\Facades\Http;

class ShipshopyController extends Controller
{
    public function create()
    {
        return view('seller.couriers.settings.shipshopy.create');
    }

   
    public function store(Request $request)
    {
        $companyId = session('company_id');
        $post_data = $request->all();
        $post_data['courier_details'] = json_encode($request->all());
        $post_data['company_id'] = $companyId;
        $validatedData = $request->validate([
            'courier_title' => 'required|string|max:50',
            'shipshopy_courier_id' => 'nullable|integer',
            'order_type' => 'required',
            'api_public_key' => 'required|string|max:50',
            'api_private_key' => 'required|string|max:50',
            'status' => 'required|boolean',            
            'env_type' => 'required|string|in:dev,live',
        ]);
        $courier=Courier::create([
            'company_id'=> $companyId,
            'parent_id'=>'13',
            'name'=>$validatedData['courier_title'],
            'status'=> $validatedData['status'],
            'image_url'=> 'images/couriers/shipshopy.png',
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
            $shipshopy = CourierSetting::where(['courier_id' => $id, 'company_id' => $companyId])->firstOrFail();
            $courier_details = json_decode($shipshopy->courier_details,true);            
            $fields = [
                'shipshopy_courier_id',
                'order_type',
                'api_public_key',
                'api_private_key',
            ];

            foreach ($fields as $field) {
                $shipshopy->{$field} = $courier_details[$field] ?? '';
            }
            return view('seller.couriers.settings.shipshopy.edit', compact('shipshopy'));
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
            'shipshopy_courier_id' => 'nullable|integer',
            'order_type' => 'required',
            'api_public_key' => 'required|string|max:50',
            'api_private_key' => 'required|string|max:50',
            'status' => 'required|boolean',            
            'env_type' => 'required|string|in:dev,live',
        ]);
        // Find the CourierSetting by courier_id
        try {
            $shipshopy = CourierSetting::where(['courier_id'=>$id,'company_id'=>$companyId])->firstOrFail();
            unset($post_data['courier_title']);
            // Update the CourierSetting
            $shipshopy->update($post_data);

            // Update the associated Courier
            $courier = Courier::where(['id'=>$shipshopy->courier_id,'company_id'=>$companyId])->firstOrFail();
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