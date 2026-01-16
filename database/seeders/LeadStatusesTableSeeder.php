<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class LeadStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('lead_statuses')->insert([
            ['id' => 1, 'status_name' => 'New', 'status_mapping' => 'fresh_lead', 'status' => 1],
            ['id' => 2, 'status_name' => 'Contacted', 'status_mapping' => 'qualified', 'status' => 1],
            ['id' => 3, 'status_name' => 'Interested', 'status_mapping' => 'qualified', 'status' => 1],
            ['id' => 4, 'status_name' => 'Proposal Sent', 'status_mapping' => 'qualified', 'status' => 1],
            ['id' => 5, 'status_name' => 'Negotiation', 'status_mapping' => 'qualified', 'status' => 1],
            ['id' => 6, 'status_name' => 'Won', 'status_mapping' => 'qualified', 'status' => 1],
            ['id' => 7, 'status_name' => 'Lost', 'status_mapping' => 'lost', 'status' => 1],
            ['id' => 8, 'status_name' => 'Unqualified', 'status_mapping' => 'unqualified', 'status' => 1],
        ]);
    }
}
