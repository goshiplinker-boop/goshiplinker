<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class OrderStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('order_statuses')->insert([
            ['status_code' => 'N', 'status_name' => 'New', 'status' => 1],
            ['status_code' => 'P', 'status_name' => 'Ready to Ship', 'status' => 1],
            ['status_code' => 'M', 'status_name' => 'Manifested', 'status' => 1],
            ['status_code' => 'S', 'status_name' => 'Shipped', 'status' => 1],
            ['status_code' => 'H', 'status_name' => 'Hold', 'status' => 1],
            ['status_code' => 'R', 'status_name' => 'Refunded', 'status' => 1],
            ['status_code' => 'C', 'status_name' => 'Canceled', 'status' => 1],
            ['status_code' => 'F', 'status_name' => 'Completed', 'status' => 1],
            ['status_code' => 'A', 'status_name' => 'Archive', 'status' => 1],
        ]);
    }
}