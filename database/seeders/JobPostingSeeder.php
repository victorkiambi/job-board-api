<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\JobPosting;

class JobPostingSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('company_name', 'Acme Tech Solutions')->first();
        if (!$company) return;

        JobPosting::updateOrCreate(
            [ 'title' => 'Backend Developer', 'company_id' => $company->id ],
            [
                'description' => 'Join our team to build scalable backend APIs and services.',
                'location' => 'Remote',
                'salary_min' => 70000,
                'salary_max' => 120000,
                'job_type' => 'full_time',
                'status' => 'active',
                'expires_at' => now()->addMonths(2),
            ]
        );
    }
} 