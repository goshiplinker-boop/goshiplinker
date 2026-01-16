<?php

namespace Database\Seeders;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Plan;
use App\Models\PlanDuration;

class PlanDurationSeeder extends Seeder
{
    public function run(): void
    {
        // Retrieve all plans
        $plans = Plan::all();

        foreach ($plans as $plan) {
            if ($plan->name === 'Free') {
                // Add trial plan duration (only 1 month)
                PlanDuration::create([
                    'plan_id' => $plan->id,
                    'duration_months' => 1,
                    'shipment_credits' => 30,
                    'total_amount' => 0.00,
                    'discount' => null,
                    'status'=>true,
                ]);
            } elseif ($plan->name === 'Trial') {
                // Add free plan duration (only 1 month)
                PlanDuration::create([
                    'plan_id' => $plan->id,
                    'duration_months' => 0,
                    'shipment_credits' => 0,
                    'total_amount' => 0.00,
                    'discount' => null,
                    'status'=>true,
                ]);
            } else {      
                $plan_status=false;          
                if($plan->id==3){
                    $shipment_credits = 150;                    

                }
                if($plan->id==4){
                    $shipment_credits = 1000;

                }
                if($plan->id==5){
                    $plan_status = true;
                    $shipment_credits = 1000;

                }
                // Add paid durations for other plans
                PlanDuration::create([
                    'plan_id' => $plan->id,
                    'duration_months' => 1,
                    'shipment_credits' => $shipment_credits,
                    'total_amount' => $plan->price_per_month,
                    'discount' => null,
                    'status'=>$plan_status,
                ]);
                PlanDuration::create([
                    'plan_id' => $plan->id,
                    'duration_months' => 3,
                    'shipment_credits' => $shipment_credits*3,
                    'total_amount' => $plan->price_per_month*3,
                    'discount' => null,
                    'status'=>false,
                ]);

                PlanDuration::create([
                    'plan_id' => $plan->id,
                    'duration_months' => 6,
                    'shipment_credits' => $shipment_credits*6,
                    'total_amount' => $plan->price_per_month*6,
                    'discount' => null,
                    'status'=>false,
                ]);
                if($plan->id==3){
                    $discount = 8;                    

                }
                if($plan->id==4){
                    $discount = 10;

                }
                if($plan->id==5){
                    $discount = 5;

                }
                PlanDuration::create([
                    'plan_id' => $plan->id,
                    'duration_months' => 12,
                    'shipment_credits' => $shipment_credits*12,
                    'total_amount' => round($plan->price_per_month*12 -($plan->price_per_month*12)*($discount/100)),
                    'discount' => $discount,
                    'status'=>$plan_status,
                ]);
            }
        }
    }
}
