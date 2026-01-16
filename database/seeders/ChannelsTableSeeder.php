<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ChannelsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('channels')->insert([
            [
                'id' => 1,
                'name' => 'Custom',
                'parent_id' => null,
                'company_id' => 0,
                'image_url' => 'images/channels/custom.png',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Shopify',
                'parent_id' => null,
                'company_id' => 0,
                'image_url' => 'images/channels/shopify.png',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Woocommerce',
                'parent_id' => null,
                'company_id' => 0,
                'image_url' => 'images/channels/woocommerce.png',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Shopbase',
                'parent_id' => null,
                'company_id' => 0,
                'image_url' => 'images/channels/shopbase.png',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'OpenCart',
                'parent_id' => null,
                'company_id' => 0,
                'image_url' => 'images/channels/opencart.png',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],           
            [
                'id' => 6,
                'name' => 'Wix',
                'parent_id' => null,
                'company_id' => 0,
                'image_url' => 'images/channels/wix.png',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
      ]);
    }
}
