<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,  // Ensure unique email
            'role_id' => 1,  // Set the default role (adjust as needed)
            'company_id' => \App\Models\Company::factory(),
            'password' => bcrypt('password'),  // Default password (hash it properly if needed)
            'email_verified_at' => now(),
        ];
    }
}
