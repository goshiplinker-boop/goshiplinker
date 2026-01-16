<?php

namespace App\Http\Controllers\Seller\Couriers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ShippingRateService;
use App\Models\Company;
use App\Models\Courier;
use App\Models\SellersCourier;
use App\Models\Order;

class ShippingRateCalculaterController extends Controller
{
    public function calculate(Request $request)
    {//return $request->all();

        // $data = app(ShippingRateService::class)->calculate(
        //     $request->company_id,
        //     $request->courier_id,
        //     $request->origin_pincode,
        //     $request->destination_pincode,
        //     $request->weight,
        //     $request->length,
        //     $request->breadth,
        //     $request->height,
        //     $request->is_cod
        // );

        // return response()->json($data);
    }
    public function showRateCalculator()
    {
        $role_id = session('role_id')??2;
        $company_id = ($role_id==1)?session('company_id'):session('parent_company_id');
        $companies = Company::with('user')->where('parent_id',$company_id)->where('status',1)->get();
        $couriers  = Courier::where('status',1)->where('company_id',$company_id)->get();
        return view('seller.couriers.shipping_rate_calculater.rate_calculator', compact('companies', 'couriers','role_id'));
    }
    public function compareCouriers(Request $request)
    {
        $companyId = session('company_id') ?? 0;
        $role_id = session('role_id') ?? 2;

        $request->validate([
            'origin_pincode'     => 'required',
            'destination_pincode'=> 'required',
            'weight'             => 'required|numeric',
            'length'             => 'required|numeric|min:1',
            'breadth'            => 'required|numeric|min:1',
            'height'             => 'required|numeric|min:1',
            'is_cod'             => 'required',
            'amount'             => 'required|numeric',
            'courier_id'         => 'nullable',
            'seller_company_id'  => 'nullable',
        ]);

        
        $seller_company_id = $request->seller_company_id ?? 0;    
        if($role_id==2){
            $seller_company_id=$companyId;
        }    
        
        $courierFilter = $request->courier_id;
        if ($seller_company_id > 0) {

            $couriers = Courier::join('sellers_couriers as sc', 'sc.courier_id', '=', 'couriers.id')
                ->where('sc.company_id', $seller_company_id)
                ->where('sc.main_courier_status', 1)
                ->where('sc.seller_courier_status', 1)
                ->where('couriers.status', 1)
                ->when($courierFilter, function ($query) use ($courierFilter) {
                    $query->where('couriers.id', $courierFilter);
                })
                ->select('couriers.*')
                ->get();

        } else {          

            $couriers = Courier::where('status', 1)
                ->when($courierFilter, function ($query) use ($courierFilter) {
                    $query->where('couriers.id', $courierFilter);
                })
                ->get();
        }
        \Log::info($couriers);
        if ($couriers->isEmpty()) {
            return response()->json([]);
        }
        

        $rateService = app(ShippingRateService::class);
        $results = [];
        if($role_id==2){
            $companyId = session('parent_company_id') ?? 0;
        }
        foreach ($couriers as $courier) {
            $rate = $rateService->calculate(
                $companyId,
                $seller_company_id,
                $courier->id,
                $courier->parent_id,                    
                $request->origin_pincode,
                $request->destination_pincode,
                $request->weight,
                $request->length,
                $request->breadth,
                $request->height,
                (int) $request->is_cod,
                $request->amount
            );

            if (!isset($rate['shipping_cost'])) {
                continue;
            }
            $results[] = [
                'courier_id'        => $courier->id,
                'courier_name'      => $courier->name,
                'logo'              => $courier->logo_url,
                'eta'               => $rate['delivery_sla'],
                'cost'              => $rate['shipping_cost'],
                'chargeable_weight' => $rate['chargeable_weight'],
                'cod_allowed'       => $courier->cod_allowed ?? false,
            ];
        }

        // Sort by lowest cost
        $results = collect($results)->sortBy('cost')->values()->toArray();

        // Mark fastest courier
        if (!empty($results)) {
            $minEta = collect($results)->min(fn($c) => intval($c['eta']));

            foreach ($results as &$item) {
                $item['is_fastest'] = intval($item['eta']) === intval($minEta);
            }
        }
        return response()->json($results);
    }


}
