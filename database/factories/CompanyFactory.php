<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'company_name' => $this->faker->company,
            'description' => $this->faker->catchPhrase,
            'website' => $this->faker->url,
            'location' => $this->faker->city,
            'logo_path' => $this->faker->imageUrl(200, 200, 'business', true, 'logo'),
        ];
    }
} 