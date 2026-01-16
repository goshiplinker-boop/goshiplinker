<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countries = [
            ['country_name' => 'Australia', 'country_code' => 'AU', 'alpha_3' => 'AUS', 'dialing_code' => '61'],
            ['country_name' => 'Austria', 'country_code' => 'AT', 'alpha_3' => 'AUT', 'dialing_code' => '43'],
            ['country_name' => 'Belgium', 'country_code' => 'BE', 'alpha_3' => 'BEL', 'dialing_code' => '32'],
            ['country_name' => 'Brazil', 'country_code' => 'BR', 'alpha_3' => 'BRA', 'dialing_code' => '55'],
            ['country_name' => 'Canada', 'country_code' => 'CA', 'alpha_3' => 'CAN', 'dialing_code' => '1'],
            ['country_name' => 'Czech Republic', 'country_code' => 'CZ', 'alpha_3' => 'CZE', 'dialing_code' => '420'],
            ['country_name' => 'Denmark', 'country_code' => 'DK', 'alpha_3' => 'DNK', 'dialing_code' => '45'],
            ['country_name' => 'Finland', 'country_code' => 'FI', 'alpha_3' => 'FIN', 'dialing_code' => '358'],
            ['country_name' => 'France', 'country_code' => 'FR', 'alpha_3' => 'FRA', 'dialing_code' => '33'],
            ['country_name' => 'Germany', 'country_code' => 'DE', 'alpha_3' => 'DEU', 'dialing_code' => '49'],
            ['country_name' => 'Hong Kong', 'country_code' => 'HK', 'alpha_3' => 'HKG', 'dialing_code' => '852'],
            ['country_name' => 'India', 'country_code' => 'IN', 'alpha_3' => 'IND', 'dialing_code' => '91'],
            ['country_name' => 'Ireland', 'country_code' => 'IE', 'alpha_3' => 'IRL', 'dialing_code' => '353'],
            ['country_name' => 'Italy', 'country_code' => 'IT', 'alpha_3' => 'ITA', 'dialing_code' => '39'],
            ['country_name' => 'Japan', 'country_code' => 'JP', 'alpha_3' => 'JPN', 'dialing_code' => '81'],
            ['country_name' => 'Netherlands', 'country_code' => 'NL', 'alpha_3' => 'NLD', 'dialing_code' => '31'],
            ['country_name' => 'New Zealand', 'country_code' => 'NZ', 'alpha_3' => 'NZL', 'dialing_code' => '64'],
            ['country_name' => 'Portugal', 'country_code' => 'PT', 'alpha_3' => 'PRT', 'dialing_code' => '351'],
            ['country_name' => 'Romania', 'country_code' => 'RO', 'alpha_3' => 'ROU', 'dialing_code' => '40'],
            ['country_name' => 'Singapore', 'country_code' => 'SG', 'alpha_3' => 'SGP', 'dialing_code' => '65'],
            ['country_name' => 'Spain', 'country_code' => 'ES', 'alpha_3' => 'ESP', 'dialing_code' => '34'],
            ['country_name' => 'Sweden', 'country_code' => 'SE', 'alpha_3' => 'SWE', 'dialing_code' => '46'],
            ['country_name' => 'Switzerland', 'country_code' => 'CH', 'alpha_3' => 'CHE', 'dialing_code' => '41'],
            ['country_name' => 'United Kingdom', 'country_code' => 'GB', 'alpha_3' => 'GBR', 'dialing_code' => '44'],
            ['country_name' => 'United States', 'country_code' => 'US', 'alpha_3' => 'USA', 'dialing_code' => '1'],
        ];


        DB::table('countries')->insert($countries);
    }
}
