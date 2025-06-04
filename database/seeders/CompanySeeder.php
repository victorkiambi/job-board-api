<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'alice@acmetech.com')->first();
        if (!$user) return;

        $company = Company::updateOrCreate(
            [ 'company_name' => 'Acme Tech Solutions' ],
            [
                'description' => 'A leading provider of innovative tech solutions.',
                'website' => 'https://acmetech.com',
                'location' => 'New York, NY',
                'logo_path' => null,
            ]
        );
        $company->users()->syncWithoutDetaching([$user->id]);
    }
} 