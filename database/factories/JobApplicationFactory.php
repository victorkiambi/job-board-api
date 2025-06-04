<?php

namespace Database\Factories;

use App\Models\JobApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobApplicationFactory extends Factory
{
    protected $model = JobApplication::class;

    public function definition(): array
    {
        return [
            'user_id' => 1, // Should be set explicitly in tests
            'job_posting_id' => 1, // Should be set explicitly in tests
            'cover_letter' => $this->faker->paragraph,
            'resume_path' => $this->faker->optional()->filePath(),
            'additional_data' => $this->faker->optional()->words(3, true),
            'status' => $this->faker->randomElement(['pending', 'reviewed', 'shortlisted', 'rejected', 'hired']),
            'applied_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
} 