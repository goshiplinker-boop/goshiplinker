<?php
namespace Database\Factories;

use App\Models\Customer;
use App\Models\Company;  // Assuming you have a Company model
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition()
    {
        return [
            'company_id' => 2, 
            'fullname' => $this->faker->name,
            'email_id' => $this->faker->unique()->safeEmail,  
            'phone' => $this->faker->phoneNumber,
        ];
    }
}
