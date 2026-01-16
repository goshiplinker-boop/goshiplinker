<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class ShipmentStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        DB::table('shipment_statuses')->insert([
            ['id' => 1, 'parent_code' => 'PKP', 'code' => 'PKP', 'name' => 'Picked up', 'status' => 1, 'status_colour' => 'bg-light', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'parent_code' => 'INT', 'code' => 'INT', 'name' => 'Intransit', 'status' => 1, 'status_colour' => 'bg-primary', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'parent_code' => 'UND', 'code' => 'UND', 'name' => 'Undelivered', 'status' => 1, 'status_colour' => 'bg-danger', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'parent_code' => 'OFD', 'code' => 'OFD', 'name' => 'Out for delivery', 'status' => 1, 'status_colour' => 'bg-secondary', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'parent_code' => 'RTO', 'code' => 'RTO', 'name' => 'RTO initiated', 'status' => 1, 'status_colour' => 'bg-danger', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'parent_code' => 'DEL', 'code' => 'DEL', 'name' => 'Delivered', 'status' => 1, 'status_colour' => 'bg-success', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'parent_code' => 'RTOD', 'code' => 'RTOD', 'name' => 'RTO delivered', 'status' => 1, 'status_colour' => 'bg-success', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 8, 'parent_code' => 'UND', 'code' => 'CNA', 'name' => 'Consignee not available', 'status' => 1, 'status_colour' => 'bg-danger', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 9, 'parent_code' => 'UND', 'code' => 'CANR', 'name' => 'COD amount not ready', 'status' => 1, 'status_colour' => 'bg-danger', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 10, 'parent_code' => 'UND', 'code' => 'ADI', 'name' => 'Address incorrect', 'status' => 1, 'status_colour' => 'bg-danger', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 11, 'parent_code' => 'UND', 'code' => 'ODA', 'name' => 'Out of delivery area', 'status' => 1, 'status_colour' => 'bg-danger', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 12, 'parent_code' => 'UND', 'code' => 'CNC', 'name' => 'Customer not contactable', 'status' => 1, 'status_colour' => 'bg-danger', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 13, 'parent_code' => 'UND', 'code' => 'FDR', 'name' => 'Future delivery requested', 'status' => 1, 'status_colour' => 'bg-danger', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 14, 'parent_code' => 'UND', 'code' => 'CRD', 'name' => 'Customer refused delivery', 'status' => 1, 'status_colour' => 'bg-danger', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 15, 'parent_code' => 'NPKP', 'code' => 'PKS', 'name' => 'Pickup scheduled', 'status' => 1, 'status_colour' => 'bg-warning', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 16, 'parent_code' => 'NPKP', 'code' => 'SHB', 'name' => 'Shipment booked', 'status' => 1, 'status_colour' => 'bg-warning', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 17, 'parent_code' => 'NPKP', 'code' => 'OFP', 'name' => 'Out for pickup', 'status' => 1, 'status_colour' => 'bg-warning', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 18, 'parent_code' => 'NPKP', 'code' => 'PRS', 'name' => 'Pickup rescheduled', 'status' => 1, 'status_colour' => 'bg-warning', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 19, 'parent_code' => 'NPKP', 'code' => 'NPKP', 'name' => 'Not Picked up', 'status' => 1, 'status_colour' => 'bg-warning', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 20, 'parent_code' => 'UND', 'code' => 'ONH', 'name' => 'Hold', 'status' => 1, 'status_colour' => 'bg-danger', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 21, 'parent_code' => 'UND', 'code' => 'LOST', 'name' => 'Lost', 'status' => 1, 'status_colour' => 'bg-danger', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 22, 'parent_code' => 'UND', 'code' => 'EXC', 'name' => 'Exception', 'status' => 1, 'status_colour' => 'bg-danger', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
