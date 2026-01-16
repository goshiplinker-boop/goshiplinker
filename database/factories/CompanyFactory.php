<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Company;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'company_email_id' => 'parcelmind@gmail.com', // Unique email address
            'phone_number' => '8826857085', // Random phone number
            'state_code' => 'HR', // State name
            'country_code' => 'IND', // Country name
            'legal_registered_name' =>'parcelmind',
        ];
    }
}
