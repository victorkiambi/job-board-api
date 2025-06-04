<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create company users and their companies
        for ($i = 0; $i < 5; $i++) {
            $companyUser = User::factory()->create([
                'user_type' => 'company',
            ]);
            $company = Company::factory()->create();
            $company->users()->attach($companyUser);
        }

        // Create job seekers
        User::factory(10)->create([
            'user_type' => 'job_seeker',
        ]);
    }
} 