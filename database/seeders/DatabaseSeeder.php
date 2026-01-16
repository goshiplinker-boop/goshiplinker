<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // // Creating a single company
        // $comp = Company::factory()->create();

        // // Creating a user linked to the company
        // User::factory()->create([
        //     'name' => 'Super Admin',
        //     'role_id' => 1,
        //     'company_id' => $comp->id, // Assign the company_id from the created company
        //     'email' => 'parcelmind@gmail.com',
        //     'password' => Hash::make('admin@123'), // Make sure the password is hashed
        // ]);
    }
}
