<?php

namespace Database\Seeders;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        // Predefined plans
        $plans = [
            [   'id' => 1,
                'name' => 'Trial',
                'sales_channels' => 1,
                'couriers' => 1,
                'pickup_locations' => 1,
                'price_per_month' =>0.00,
                'setup_fee' => 0.00,
                'support_type' => null,
                'status' => true,
            ],
            [   
                'id' => 2,
                'name' => 'Free',
                'sales_channels' => 1,
                'couriers' => 1,
                'pickup_locations' => 1,
                'price_per_month' =>0.00,
                'setup_fee' => 0.00,
                'support_type' => null,
                'status' => true,
            ],
            [
                'id' => 3,
                'name' => 'Basic',
                'sales_channels' => 2,
                'couriers' => 2,
                'pickup_locations' => 2,
                'price_per_month' => 425,
                'setup_fee' => 0.00,
                'support_type' => 'Email Customer Support',
                'status' => false,
            ],
            [
                'id' => 4,
                'name' => 'Standard',
                'sales_channels' => 3,
                'couriers' => 3,
                'pickup_locations' => 3,
                'price_per_month' => 2465,
                'setup_fee' => 0.00,
                'support_type' => 'Email & Phone Support',
                'status' => false,
            ],
            [   'id' => 5,
                'name' => 'Premium',
                'sales_channels' => 5,
                'couriers' => 5,
                'pickup_locations' => 5,
                'price_per_month' => 2000,
                'setup_fee' => 0.00,
                'support_type' => 'Dedicated Account Manager',
                'status' => true,
            ],
        ];

        // Insert plans into the database
        foreach ($plans as $plan) {
            Plan::create($plan);
        }
    }
}
