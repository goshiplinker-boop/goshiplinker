<?php

namespace App\Http\Controllers\Seller\Couriers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CourierSetting;
use App\Models\Courier;


class SelfshipCourierController extends Controller
{
    public function create()
    {
        return view('seller.couriers.settings.SelfshipCourier.create');
    }


    public function store(Request $request)
    {
        $companyId = session('company_id');
        $post_data = $request->all();
        $post_data['courier_details'] = json_encode($request->all());
        $post_data['company_id'] = $companyId;
        $validatedData = $request->validate([
            'courier_title' => 'required|string|max:50',
            'status' => 'required|boolean',
        ]);
        $courier=Courier::create([
            'company_id'=> $companyId,
            'parent_id'=>'1',
            'name'=>$validatedData['courier_title'],
            'status'=> $validatedData['status'],
            'image_url'=> 'images/couriers/selfship.png',
        ]);
        $post_data['courier_id'] = $courier->id;
        CourierSetting::create($post_data);
        // Redirect with a success message
        return redirect()->route(panelPrefix().'.couriers_list')->with('success', 'Selfship setting created successfully.');
    }
    public function edit(string $id)
    {
        // Find the CourierSetting by courier_id
        $companyId = session('company_id');
        try {
            $selfship = CourierSetting::where(['courier_id' => $id, 'company_id' => $companyId])->firstOrFail();
            return view('seller.couriers.settings.SelfshipCourier.edit', compact('selfship'));
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
            'status' => 'required|boolean',
        ]);

        // Find the CourierSetting by courier_id
        try {
            $selfship = CourierSetting::where(['courier_id'=>$id,'company_id'=>$companyId])->firstOrFail();
            unset($post_data['courier_title']);
            // Update the CourierSetting
            $selfship->update($post_data);

            // Update the associated Courier
            $courier = Courier::where(['id'=>$selfship->courier_id,'company_id'=>$companyId])->firstOrFail();
            $courier->update([
               // 'name' => $validatedData['courier_title'],
                'status' => $validatedData['status']
            ]);
            return redirect()->route(panelPrefix().'.couriers_list')->with('success', $validatedData['courier_title'].' settings updated successfully.');
        }catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where no record was found
            return view('errors.404',['error' => 'courier does not exist']);
        }
    }
}
