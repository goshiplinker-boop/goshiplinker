<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class StatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('states')->insert([
            ['name' => 'Andhra Pradesh', 'state_code' => 'AD', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Arunachal Pradesh', 'state_code' => 'AR', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Assam', 'state_code' => 'AS', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Bihar', 'state_code' => 'BR', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Chhattisgarh', 'state_code' => 'CG', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Delhi', 'state_code' => 'DL', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Goa', 'state_code' => 'GA', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Gujarat', 'state_code' => 'GJ', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Haryana', 'state_code' => 'HR', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Himachal Pradesh', 'state_code' => 'HP', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Jharkhand', 'state_code' => 'JH', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Karnataka', 'state_code' => 'KA', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Kerala', 'state_code' => 'KL', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Maharashtra', 'state_code' => 'MH', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Madhya Pradesh', 'state_code' => 'MP', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Manipur', 'state_code' => 'MN', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Meghalaya', 'state_code' => 'ML', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Mizoram', 'state_code' => 'MZ', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Nagaland', 'state_code' => 'NL', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Odisha', 'state_code' => 'OD', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Punjab', 'state_code' => 'PB', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Rajasthan', 'state_code' => 'RJ', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Sikkim', 'state_code' => 'SK', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Tamil Nadu', 'state_code' => 'TN', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Tripura', 'state_code' => 'TR', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Telangana', 'state_code' => 'TS', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Uttar Pradesh', 'state_code' => 'UP', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Uttarakhand', 'state_code' => 'UK', 'country_code' => 'IN', 'status' => true],
            ['name' => 'West Bengal', 'state_code' => 'WB', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Andaman and Nicobar', 'state_code' => 'AN', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Chandigarh', 'state_code' => 'CH', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Dadra and Nagar Haveli and Daman and Diu', 'state_code' => 'DN', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Jammu and Kashmir', 'state_code' => 'JK', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Ladakh', 'state_code' => 'LA', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Lakshadweep', 'state_code' => 'LD', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Puducherry', 'state_code' => 'PY', 'country_code' => 'IN', 'status' => true],
            ['name' => 'Other', 'state_code' => 'OT', 'country_code' => 'AUS', 'status' => true],
            ['name' => 'Other', 'state_code' => 'OT', 'country_code' => 'AUT', 'status' => true],
            ['name' => 'Other', 'state_code' => 'OT', 'country_code' => 'BEL', 'status' => true],
            ['name' => 'Other', 'state_code' => 'OT', 'country_code' => 'BRA', 'status' => true],
            ['name' => 'Other', 'state_code' => 'OT', 'country_code' => 'CAN', 'status' => true],
            ['name' => 'Other', 'state_code' => 'OT', 'country_code' => 'CZE', 'status' => true],
            ['name' => 'Other', 'state_code' => 'OT', 'country_code' => 'DNK', 'status' => true],
            ['name' => 'Other', 'state_code' => 'OT', 'country_code' => 'FIN', 'status' => true],
            ['name' => 'Other', 'state_code' => 'OT', 'country_code' => 'FRA', 'status' => true],
            ['name' => 'Other', 'state_code' => 'OT', 'country_code' => 'DEU', 'status' => true],
            ['name' => 'Other', 'state_code' => 'OT', 'country_code' => 'HKG', 'status' => true],
            ['name' => 'Other', 'state_code' => 'OT', 'country_code' => 'IRL', 'status' => true],
            ['name' => 'Other', 'state_code' => 'OT', 'country_code' => 'ITA', 'status' => true],
            ['name' => 'Other', 'state_code' => 'OT', 'country_code' => 'JPN', 'status' => true],
            ['name' => 'Other', 'state_code' => 'OT', 'country_code' => 'NLD', 'status' => true],
            ['name' => 'Other', 'state_code' => 'OT', 'country_code' => 'NZL', 'status' => true],
            ['name' => 'Other', 'state_code' => 'OT', 'country_code' => 'PRT', 'status' => true],
            ['name' => 'Other', 'state_code' => 'OT', 'country_code' => 'ROU', 'status' => true],
            ['name' => 'Other', 'state_code' => 'OT', 'country_code' => 'SGP', 'status' => true],
            ['name' => 'Other', 'state_code' => 'OT', 'country_code' => 'ESP', 'status' => true],
            ['name' => 'Other', 'state_code' => 'OT', 'country_code' => 'SWE', 'status' => true],
            ['name' => 'Other', 'state_code' => 'OT', 'country_code' => 'CHE', 'status' => true],
            ['name' => 'Other', 'state_code' => 'OT', 'country_code' => 'GBR', 'status' => true],
            ['name' => 'Other', 'state_code' => 'OT', 'country_code' => 'USA', 'status' => true]
        ]);
    
    }
}
