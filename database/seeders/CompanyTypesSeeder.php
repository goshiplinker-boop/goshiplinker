<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CompanyTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'type' => 'Individual',
                'subtype' => 'Individual',
                'status' => 1,
            ],
            [
                'type' => 'Individual',
                'subtype' => 'HUF',
                'status' => 1,
            ],
            [
                'type' => 'Sole Proprietor',
                'subtype' => null,
                'status' => 1,
            ],
            [
                'type' => 'Company',
                'subtype' => 'Partnership',
                'status' => 1,
            ],
            [
                'type' => 'Company',
                'subtype' => 'Limited Liability Partnership',
                'status' => 1,
            ],
            [
                'type' => 'Company',
                'subtype' => 'Public Limited Company',
                'status' => 1,
            ],
            [
                'type' => 'Company',
                'subtype' => 'Private Limited Company',
                'status' => 1,
            ],
            [
                'type' => 'Company',
                'subtype' => 'Trust',
                'status' => 1,
            ],
        ];

        DB::table('company_types')->insert($data);
    }
}
