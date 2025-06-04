<?php

namespace Database\Factories;

use App\Models\JobPosting;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobPostingFactory extends Factory
{
    protected $model = JobPosting::class;

    public function definition(): array
    {
        return [
            'company_id' => 1,
            'title' => $this->faker->jobTitle,
            'description' => $this->faker->paragraph,
            'location' => $this->faker->city,
            'salary_min' => $this->faker->numberBetween(30000, 60000),
            'salary_max' => $this->faker->numberBetween(60001, 120000),
            'job_type' => $this->faker->randomElement(['full_time', 'part_time', 'contract', 'internship']),
            'status' => $this->faker->randomElement(['active', 'inactive', 'filled']),
            'expires_at' => $this->faker->optional()->dateTimeBetween('+1 week', '+6 months'),
        ];
    }
} 