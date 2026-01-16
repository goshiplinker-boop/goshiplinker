<?php

namespace App\Http\Controllers\Seller\Couriers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CourierSetting;
use App\Models\Courier;
class DelhiveryController extends Controller
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
        return view('seller.couriers.settings.delhivery.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $companyId = session('company_id');
        $post_data = $request->all();
        $post_data['courier_details'] = json_encode($request->all());
        $post_data['company_id'] = $companyId;
        $validatedData = $request->validate([
            'courier_title' => 'required|string|max:50',
            'api_token' => 'required|string|max:255',
            'status' => 'required|boolean',
            'env_type' => 'required|string|in:dev,live',
        ]);
        $courier=Courier::create([
            'company_id'=> $companyId,
            'parent_id'=>'3',
            'name'=>$validatedData['courier_title'],
            'status'=> $validatedData['status'],
            'image_url'=> 'images/couriers/delhivery.png',
        ]);
        $post_data['courier_id'] = $courier->id;
        CourierSetting::create($post_data);
        // Redirect with a success message
        return redirect()->route(panelPrefix().'.couriers_list')->with('success', 'courier setting created successfully.');
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
        // Find the CourierSetting by courier_id
        $companyId = session('company_id');
        try {
            $delhivery = CourierSetting::where(['courier_id' => $id, 'company_id' => $companyId])->firstOrFail();
            $courier_details = json_decode($delhivery->courier_details);
            $delhivery->shipment_mode = $courier_details->shipment_mode;
            $delhivery->api_token = $courier_details->api_token;
            return view('seller.couriers.settings.delhivery.edit', compact('delhivery'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where no record was found
            return view('errors.404',['error' => 'courier does not exist']);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $companyId = session('company_id');

        $post_data = $request->all();
        $post_data['courier_details'] = json_encode($request->all());
        $post_data['company_id'] = $companyId;
        // Validate incoming data
        $validatedData = $request->validate([
            'courier_title' => 'required|string|max:50',
            'api_token' => 'required|string|max:255',
            'status' => 'required|boolean',
            'shipment_mode' => 'required|in:surface,express',
            'env_type' => 'required|string|in:dev,live',
        ]);

        // Find the CourierSetting by courier_id
        try {
            $delhivery = CourierSetting::where(['courier_id'=>$id,'company_id'=>$companyId])->firstOrFail();
            unset($post_data['courier_title']);
            // Update the CourierSetting
            $delhivery->update($post_data);

            // Update the associated Courier
            $courier = Courier::where(['id'=>$delhivery->courier_id,'company_id'=>$companyId])->firstOrFail();
            $courier->update([
               // 'name' => $validatedData['courier_title'],
                'status' => $validatedData['status'],
            ]);
            return redirect()->route(panelPrefix().'.couriers_list')->with('success', $validatedData['courier_title'].' settings updated successfully.');
        }catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where no record was found
            return view('errors.404',['error' => 'courier does not exist']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
