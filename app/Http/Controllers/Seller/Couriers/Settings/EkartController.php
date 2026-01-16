<?php

namespace App\Http\Controllers\Seller\Couriers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CourierSetting;
use App\Models\Courier;
class EkartController extends Controller
{
    public function create()
    {
        return view('seller.couriers.settings.ekart.create');
    }
    public function store(Request $request)
    {
        $companyId = session('company_id');
        $post_data = $request->all();
        $post_data['courier_details'] = json_encode($request->all());
        $post_data['company_id'] = $companyId;
        $validatedData = $request->validate([
            'courier_title' => 'required|string|max:50',
            'merchant_code' => 'required|string|max:100',
            'authorization_code' => 'required|string|max:100',
            'goods_category' => 'required|string|in:NON_ESSENTIAL,ESSENTIAL',
            'service_code' => 'required|string|in:REGULAR,ECONOMY,NDD',
            'delivery_type' => 'required|string|in:SMALL,MEDIUM,LARGE',
            'status' => 'required|boolean',
            'env_type' => 'required|string|in:dev,live',
        ]);
        $courier=Courier::create([
            'company_id'=> $companyId,
            'parent_id'=>'4',
            'name'=>ucfirst($validatedData['courier_title']),
            'status'=> $validatedData['status'],
            'image_url'=> 'images/couriers/ekart.png',
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
            $ekart = CourierSetting::where(['courier_id' => $id, 'company_id' => $companyId])->firstOrFail();
            $courier_details = json_decode($ekart->courier_details);
            $ekart->merchant_code = $courier_details->merchant_code;
            $ekart->authorization_code = $courier_details->authorization_code;
            $ekart->goods_category = $courier_details->goods_category;
            $ekart->service_code = $courier_details->service_code;
            $ekart->delivery_type = $courier_details->delivery_type;
            //return $ekart;
            return view('seller.couriers.settings.ekart.edit', compact('ekart'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where no record was found
            return view('errors.404',['error' => 'courier does not exist']);
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
            'merchant_code' => 'required|string|max:100',
            'authorization_code' => 'required|string|max:100',
            'goods_category' => 'required|string|in:NON_ESSENTIAL,ESSENTIAL',
            'service_code' => 'required|string|in:REGULAR,ECONOMY,NDD',
            'delivery_type' => 'required|string|in:SMALL,MEDIUM,LARGE',
            'status' => 'required|boolean',
            'env_type' => 'required|string|in:dev,live',
        ]);

        // Find the CourierSetting by courier_id
        try {
            $ekart = CourierSetting::where(['courier_id'=>$id,'company_id'=>$companyId])->firstOrFail();
            unset($post_data['courier_title']);
            // Update the CourierSetting
            $ekart->update($post_data);

            // Update the associated Courier
            $courier = Courier::where(['id'=>$ekart->courier_id,'company_id'=>$companyId])->firstOrFail();
            $courier->update([
               // 'name' => ucfirst($validatedData['courier_title']),
                'status' => $validatedData['status'],
            ]);
            return redirect()->route(panelPrefix().'.couriers_list')->with('success', $validatedData['courier_title'].' settings updated successfully.');
        }catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where no record was found
            return view('errors.404',['error' => 'courier does not exist']);
        }
    }
}
