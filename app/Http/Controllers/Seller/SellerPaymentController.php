<?php

namespace App\Http\Controllers\Seller;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Company;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\ChannelSetting;
use App\Models\PlanDuration;
use Illuminate\Support\Facades\Session;
class SellerPaymentController extends Controller
{
    public function index()
    {   
        $company_id = session('company_id');
        $plans = Plan::with(['durations' => function ($query) {
            $query->where('status', 1);
        }])
        ->where('status', 1)
        ->whereHas('durations', function ($query) {
            $query->where('status', 1);
        })
        ->get();
    
        $montth_wise_plans = [];
        $free_plans = [];
        foreach ($plans as $plan) {        
            if($plan['id']==1){
                continue;
            }    
            foreach ($plan['durations'] as $duration) {
                $planData = $plan->toArray();
                $planData['durations'] = $duration->toArray();
                if($plan['id']==2){
                    $free_plans[] = $planData;
                }else{
                    if(!isset($montth_wise_plans[$duration['duration_months']])){
                        $montth_wise_plans[$duration['duration_months']] = $free_plans;
                    }
                    $montth_wise_plans[$duration['duration_months']][] = $planData;
                }                
                
            }
        }    
        $is_shopify_user=false;
        $is_other_user=false;
        $shopify_channels = ChannelSetting::where([
            ['company_id', '=', $company_id],
            ['status', '=', 1],
            ['channel_code', '=', 'shopify']
        ])->get();
        $other_channels = ChannelSetting::where([
            ['company_id', '=', $company_id],
            ['status', '=', 1],
            ['channel_code', '!=', 'shopify']
        ])->get();

        if ($shopify_channels->isNotEmpty()) {
            $is_shopify_user = true;
        }
        if ($other_channels->isNotEmpty()) {
            $is_other_user = true;
        }
        $company_settings = Company::where('id', $company_id)->first();

        if ($company_settings) {      
            Session::put("subscription_status", $company_settings->subscription_status);
            Session::put("subscription_plan", $company_settings->subscription_plan);
        }
        // Pass plans to the view
        return view('seller.seller_payments.subscription_plans', ['plans' => $montth_wise_plans,'shopify_channels'=>$shopify_channels,'is_shopify_user'=>$is_shopify_user,'is_other_user'=>$is_other_user]);
    }
    public function trial()
    {
        $json = [];     

        if (session()->has('company_id')) {
            $company_id = session('company_id');
            $subscription_plan = session('subscription_plan');
            $subscription_status = session('subscription_status');
            if (is_null($subscription_plan)) {    
                DB::beginTransaction(); 
                try {
                    // Insert a trial subscription
                    Subscription::create([
                        'company_id' => $company_id,
                        'plan_id' => 1,
                        'expiry_date' => date('Y-m-d', strtotime('+7 days')),
                    ]);

                    // Update company with trial details
                    Company::where('id', $company_id)->update([
                        'subscription_plan' => 'Trial',
                        'subscription_status' => 1
                    ]);

                    // Update session with new subscription details
                    session([
                        'subscription_plan' => 'trial',
                        'subscription_status' => 1
                    ]);

                    $json['success'] = 'Trial has been activated successfully.';
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    Log::error("An error occurred when activating trial: " . $e->getMessage());
                    $json['error'] = 'Failed to activate the trial. Please try again.';
                }
            } else {
                $json['error'] = 'You can take trial only once.';
            }
        } else {
            $json['error'] = 'Access denied.';
        }

        return redirect()->back()->with($json);
    }
    public function free()
    {
        $json = [];     

        if (session()->has('company_id')) {
            $company_id = session('company_id');
            $subscription_plan = session('subscription_plan');
            $subscription_status = session('subscription_status');
            if ($subscription_plan !='free' || ($subscription_plan =='free' && $subscription_status==0)) {    
                DB::beginTransaction(); 
                try {
                    $plan = PlanDuration::where('plan_id',2)->first();
                    // Insert a trial subscription
                    Subscription::create([
                        'company_id' => $company_id,
                        'plan_id' => 2,
                        'purchased_credits' => $plan->shipment_credits,
                        'expiry_date' => date('Y-m-d', strtotime('+1 months')),
                    ]);

                    // Update company with trial details
                    Company::where('id', $company_id)->update([
                        'subscription_plan' => 'Free',
                        'subscription_status' => 1
                    ]);

                    // Update session with new subscription details
                    session([
                        'subscription_plan' => 'free',
                        'subscription_status' => 1
                    ]);

                    $json['success'] = 'Free plan has been activated successfully.';
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    Log::error("An error occurred when activating free: " . $e->getMessage());
                    $json['error'] = 'Failed to activate the free plan. Please try again.';
                }
            } else {
                $json['error'] = 'already You have free plan';
            }
        } else {
            $json['error'] = 'Access denied.';
        }

        return redirect()->back()->with($json);
    }

}
