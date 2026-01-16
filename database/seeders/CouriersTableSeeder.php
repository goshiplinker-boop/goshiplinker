<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CouriersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('couriers')->insert([
            [   
                'id' => 1,
                'name' => 'SelfShip',
                'parent_id' => null,
                'company_id' => 0,
                'image_url' => 'images/couriers/selfship.png',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Bluedart',
                'parent_id' => null,
                'company_id' => 0,
                'image_url' => 'images/couriers/bluedart.png',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Delhivery',
                'parent_id' => null,
                'company_id' => 0,
                'image_url' => 'images/couriers/delhivery.png',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Ekart',
                'parent_id' => null,
                'company_id' => 0,
                'image_url' => 'images/couriers/ekart.png',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Dtdc',
                'parent_id' => null,
                'company_id' => 0,
                'image_url' => 'images/couriers/dtdc.png',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'Xpressbees Postpaid',
                'parent_id' => null,
                'company_id' => 0,
                'image_url' => 'images/couriers/xpressbees_postpaid.png',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'name' => 'Xpressbees Prepaid',
                'parent_id' => null,
                'company_id' => 0,
                'image_url' => 'images/couriers/xpressbees_prepaid.png',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],            
            [
                'id' => 8,
                'name' => 'Shiprocket',
                'parent_id' => null,
                'company_id' => 0,
                'image_url' => 'images/couriers/shiprocket.png',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'name' => 'Shipway',
                'parent_id' => null,
                'company_id' => 0,
                'image_url' => 'images/couriers/shipway.png',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 10,
                'name' => 'Nimbus Post',
                'parent_id' => null,
                'company_id' => 0,
                'image_url' => 'images/couriers/nimbus_post.png',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 11,
                'name' => 'Dtdc Ltl',
                'parent_id' => null,
                'company_id' => 0,
                'image_url' => 'images/couriers/dtdc_ltl.png',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 12,
                'name' => 'Rapidshyp',
                'parent_id' => null,
                'company_id' => 0,
                'image_url' => 'images/couriers/rapidshyp.png',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],            
            [
                'id' => 13,
                'name' => 'Shipshopy',
                'parent_id' => null,
                'company_id' => 0,
                'image_url' => 'images/couriers/shipshopy.png',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
            
        ]);
    }
}
