<?php

namespace App\Http\Controllers\Seller\Couriers\Fulfillment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CourierSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ShipmentInfo;
use App\Models\Order;
use App\Models\Courier;
use App\Services\SellerWalletService;
class AssignTrackingNumber extends Controller
{
    public function assign(Request $request){
        //return $request->all();
        $company_id = session('company_id');    
        $parent_company_id = session('parent_company_id');       
        $courier_id = $request->courier_id??0;
        $order_ids = $request->order_ids??[];
        $pickup_location_id = $request->pickup_location_id??0;
        $return_pickup_location_id = $request->return_pickup_location_id??0;   
        $action = $request->print_response??""; 
        if(empty($order_ids)){
            session()->flash('error','Please select orders to assign tracking number.');
            return;
			//return  redirect()->route('order_list')->with('error','Please select orders to assign tracking number.');			
		}
        // $courier_settings = CourierSetting::where('company_id', $company_id)
        // ->where('courier_id', $courier_id)
        // ->where('status', 1)
        // ->first();
        $courier_settings = CourierSetting::with('sellersCouriers')
            ->where('status', 1)
            ->where('company_id', $parent_company_id)
            ->whereHas('sellersCouriers', function ($q) use ($company_id,$courier_id) {
                $q->where('seller_courier_status', 1)
                ->where('main_courier_status', 1)
                ->where('company_id', $company_id) // seller's company
                ->where('courier_id', $courier_id); // seller's courier
            })
        ->first();
        if(empty($courier_settings)){
            session()->flash('error', 'Courier is invalid');
            return;
          	
        }
        $allowedOrders = DB::table('orders')
        ->where('status_code', 'N')
        ->whereIn('id', $order_ids)
        ->pluck('id')
        ->toArray();  
        $orderids = array_intersect($allowedOrders, $order_ids);
        if(empty($orderids)){
            session()->flash('error','order not found for assigning tracking no');
            return;
            //return  redirect()->route('order_list')->with('error','order not found for assigning tracking no');
        }
        //Log::info($orderids);	
        $pickup_address = DB::table('pickup_locations')
        ->join('companies', 'pickup_locations.company_id', '=', 'companies.id')
        ->where('pickup_locations.company_id', $company_id)
        ->where('pickup_locations.id', $pickup_location_id)
        ->where('pickup_locations.status', 1)
        ->select('pickup_locations.*')
        ->first();
       //return $pickup_address;
        if(empty($pickup_address)){
            session()->flash('error', 'pickup_address does not exist');
            return;
            //return  redirect()->route('order_list')->with('error','pickup_address does not exist');
        }
        if($return_pickup_location_id != $pickup_location_id){
            $return_address = DB::table('pickup_locations')
            ->join('companies', 'pickup_locations.company_id', '=', 'companies.id')
            ->where('pickup_locations.company_id', $company_id)
            ->where('pickup_locations.id', $return_pickup_location_id)
            ->where('pickup_locations.status', 1)
            ->select('pickup_locations.*')
            ->first();
            if(empty($return_address)){
                session()->flash('error','return_address does not exist');
                return;
                //return  redirect()->route('order_list')->with('error','return_address does not exist');
            }
        }else{
            $return_address = $pickup_address;
        }
        // Dynamically select the controller based on courier code
        $class = '\App\Http\Controllers\Seller\Couriers\Fulfillment\\' .str_replace(' ', '', ucwords(str_replace('_', ' ', $courier_settings->courier_code))) . 'CourierController';
        $controller = app()->make($class, ['order_ids'=>$orderids,'courier_id'=>$courier_id,'company_id'=>$company_id,'courier_settings' => $courier_settings]);
        $controller->pickup_address=$pickup_address;
        $controller->return_address=$return_address;
        $controller->action=$action;
        $response = $controller->assignTrackingNumber(); 
        if(isset($response['print_response'])){
            return $response;
        }         
        if(isset($response['error'])){
            if(count($order_ids)>1){
                $errorFile = $this->createErrorCsv($response['error']);
                $errorFile = route("download_error_csv", [
                        "filename" => $errorFile,
                    ]);
                $error = 'Some orders were not assigned. <a href="' .$errorFile .'" target="_blank">Click here to download the file with error remarks.</a>';

            }else{
                $error = implode(",", $response['error']); 
            }
            
            session()->flash('error',$error);
        }
        if(isset($response['success']) && $response['success']){
           session()->flash('success', 'Tracking number assigned successfully!');
        }
        return;
        //return  redirect()->route('order_list')->with($json);
    }
    public function unassign(Request $request)
    {
        $company_id = session('company_id');
        $parent_company_id = session('parent_company_id');  
        $order_ids = $request->order_ids ?? [];
    
        if (empty($order_ids)) {
            session()->flash('error', 'Please select orders to unassign tracking number.');
            return;
        }
        if ($order_ids && !is_array($order_ids)) {
            $order_ids = [$order_ids];
        }
    
        // Get all matching orders that are in status P
        $allowedOrders = DB::table('orders')
            ->join('shipment_info', 'orders.id', '=', 'shipment_info.order_id')
            ->where('orders.status_code', 'P')
            ->whereIn('orders.id', $order_ids)
            ->select('orders.id', 'shipment_info.courier_id')
            ->get();
    
        if ($allowedOrders->isEmpty()) {
            session()->flash('error', 'No eligible orders found for unassigning tracking number.');
            return;
        }
    
        // Group orders by courier_id
        $allorders = [];
        foreach ($allowedOrders as $allowedOrder) {
            $allorders[$allowedOrder->courier_id][] = $allowedOrder->id;
        }
    
        $errors = [];
        $result = [];
    
        foreach ($allorders as $courier_id => $allowedOrderids) {
            // Fetch courier settings
            // $courier_settings = CourierSetting::where('company_id', $company_id)
            //     ->where('courier_id', $courier_id)
            //     ->where('status', 1)
            //     ->first();
            $courier_settings = CourierSetting::with('sellersCouriers')
                ->where('status', 1)
                ->where('company_id', $parent_company_id)
                ->whereHas('sellersCouriers', function ($q) use ($company_id,$courier_id) {
                    $q->where('seller_courier_status', 1)
                    ->where('main_courier_status', 1)
                    ->where('company_id', $company_id) // seller's company
                    ->where('courier_id', $courier_id); // seller's courier
                })
            ->first();
    
            if (empty($courier_settings)) {
                $errors[] = "Courier ID {$courier_id} is invalid or inactive.";
                continue;
            }
    
            // Dynamically load the courier controller
            $class = '\App\Http\Controllers\Seller\Couriers\Fulfillment\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $courier_settings->courier_code))) . 'CourierController';
    
            if (!class_exists($class)) {
                $errors[] = "Courier controller for {$courier_settings->courier_code} not found.";
                continue;
            }
    
            try {
                $controller = app()->make($class, [
                    'order_ids' => $allowedOrderids,
                    'courier_id' => $courier_id,
                    'company_id' => $company_id,
                    'courier_settings' => $courier_settings
                ]);
    
                $response = $controller->cancelShipments();
                if (isset($response['error'])) {
                    $errors = array_merge($errors,$response['error']);
                }
    
                if (isset($response['success']) && !empty($response['success'])) {                    
                    $msg = $response['success']??'';
                    $result['success'] = is_array($msg)?implode("<br>", $msg):$msg;
                }
            } catch (\Throwable $e) {
                $errors[] = "Exception for Courier ID {$courier_id}: " . $e->getMessage();
            }
        }
        // Flash errors if any
        if (!empty($errors)) {
            $errors =implode("<br>", $errors);    
            session()->flash('error', $errors);
        }
    
        if (isset($result['success']) && !empty($result['success'])) {
            $message = $result['success']??'Tracking number unassigned successfully!';
            session()->flash('success', $message);
        }
    
        return;
    }
    
    public function createErrorCsv($errorRows,$filename='')
    {
        $filename = ($filename)?$filename."_".time() . ".csv":"Error_tracking_" . time() . ".csv";
        $path = storage_path("app/public/" . $filename);
        $handle = fopen($path, "w");
        fputcsv($handle, [
            "Order Id",
            "Error"
        ]);
        foreach ($errorRows as $order_id=>$error) {
            fputcsv($handle, [$order_id,$error]);
        }

        fclose($handle);

        return $filename;
    }
    public function track($tracking_number,$courier_id,$company_id=0){
        $company_id = (!empty($company_id))?$company_id:session('company_id');         
        // Fetch the CourierSetting instance for the given courier_id
        $courier_settings = CourierSetting::where('courier_id', $courier_id)
            ->where('company_id', $company_id)
            ->where('status', 1)
            ->where('courier_code', '!=', 'selfship')
            ->first();

        // Check if the CourierSetting exists
        if (!$courier_settings) {
            session()->flash('error','CourierSetting not found');
            return;
        }
        $buyerSettings = BuyerShippingLabelSetting::where('company_id', $company_id)->first();
        $extra_details = json_decode($buyerSettings->extra_details ?? '{}', true);
        $auto_shipped = $extra_details->auto_shipped??1;
        if($auto_shipped==1){
            $order = ShipmentInfo::with('order')->where(
                ['courier_id'=>$courier_id,
                'tracking_id'=>$tracking_number,
                'company_id'=>$company_id
                ]
            )->first();
        }else{
            $order = ShipmentInfo::where(
                ['courier_id'=>$courier_id,
                'tracking_id'=>$tracking_number,
                'company_id'=>$company_id
                ]
            )->first();
        }
        
        if(empty($order)){
            session()->flash('error','Tracking no is not found');
            return;
        }
        $payment_type = $order->order->payment_type??'';
        $payment_type = strtolower($payment_type);
        $class = '\App\Http\Controllers\Seller\Couriers\Fulfillment\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $courier_settings->courier_code))) . 'CourierController';
        $controller = app()->make($class, ['order_ids'=>array(),'courier_id'=>$courier_id,'company_id'=>$company_id,'courier_settings' => $courier_settings]);       
        $response=array();
        // Check if the trackShipment method exists before calling it
        if (method_exists($controller, 'trackShipment')) {
            $response = $controller->trackShipment($order->order_id, $tracking_number);
            if($auto_shipped==1 && $order->order->status_code=='M' && (isset($response['current_status_code']) && in_array($response['current_status_code'],['PKP','INT','OFD','DEL']))){
               Order::where('id', $order->order_id)->update(['status_code' => 'S']);                
            }
            if($payment_type=='cod' && isset($response['current_status_code']) && $response['current_status_code']=='RTOD'){
                try {
                    app(SellerWalletService::class)->revertCodCharge([
                        'company_id'      => $order->company_id,
                        'shipment_id'     => $order->id,//shipment id
                        'tracking_number' => $order->tracking_id,
                    ]);
                } catch (\Exception $e) {
                    \Log::warning('COD revert skipped', [
                        'shipment_id' => $order->id,//shipment id
                        'reason' => $e->getMessage(),
                    ]);
                }
            }
            if (isset($response['error'])) {
                $error = is_array($response['error']) ? implode('; ', $response['error']) : $response['error'];
                session()->flash('error', $error);
            }

            if (isset($response['success']) && $response['success']) {
                session()->flash('success', 'Tracking number assigned successfully!');
            }
        } else {
            // Handle the case where the method does not exist
            session()->flash('error', 'The trackShipment method does not exist for this courier.');
        }
        return $response;    

    }
    public function calculateShipping(Request $request){        
        $company_id = session('company_id');       
        $courier_id = $request->courier_id??0;
        $order_ids = $request->order_ids??[];
        $pickup_location_id = $request->pickup_location_id??0;
        $return_pickup_location_id = $request->return_pickup_location_id??0;   
        $action = $request->print_response??"";
        $result=array();
        if(empty($order_ids)){
            $result['error'] = 'Please select orders to assign tracking number.';
            return $result;				
		}
        if(count($order_ids) > 1){
            $result['error'] = 'You can calculate shipping charges for one order at a time.';
            return $result;		
					
		}
        $courier_settings = CourierSetting::where('company_id', $company_id)
        ->where('courier_id', $courier_id)
        ->where('status', 1)
        ->first();
        if(empty($courier_settings)){
            $result['error'] = 'Courier is invalid.';
            return $result;		
                   	
        }
        
        //Log::info($orderids);	
        $pickup_address = DB::table('pickup_locations')
        ->join('companies', 'pickup_locations.company_id', '=', 'companies.id')
        ->where('pickup_locations.company_id', $company_id)
        ->where('pickup_locations.id', $pickup_location_id)
        ->where('pickup_locations.status', 1)
        ->select('pickup_locations.*')
        ->first();
       //return $pickup_address;
        if(empty($pickup_address)){
            $result['error'] = 'pickup_address does not exist';
            return $result;		
        }
       
        $return_address = $pickup_address;
        
        // Dynamically select the controller based on courier code
        $class = '\App\Http\Controllers\Seller\Couriers\Fulfillment\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $courier_settings->courier_code))) . 'CourierController';
        $controller = app()->make($class, ['order_ids'=>$order_ids,'courier_id'=>$courier_id,'company_id'=>$company_id,'courier_settings' => $courier_settings]);
        $controller->pickup_address=$pickup_address;
        $controller->return_address=$return_address;
        $controller->action=$action;
        $response = $controller->calculateShipping();  

        if(isset($response['error'])){            
            $error = implode(",", $response['error']); 
            $result['error'] = $error;
            
        }
        if(isset($response['success']) && $response['success']){
            $message = $response['message']??'';
            $result['success'] = true;
            $result['message'] = $message;
        }
        return $result;
    }

}
