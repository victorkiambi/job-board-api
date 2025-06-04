<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobPosting;
use App\Models\Company;

class JobPostingSeeder extends Seeder
{
    public function run(): void
    {
        $companyIds = Company::pluck('id');
        JobPosting::factory(20)->create([
            'company_id' => $companyIds->random(),
        ]);
    }
} 