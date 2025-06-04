<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\JobPosting;
use App\Models\JobApplication;

class JobApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'frank.miller@email.com')->first();
        $job = JobPosting::where('title', 'Backend Developer')->first();
        if (!$user || !$job) return;

        JobApplication::updateOrCreate(
            [ 'user_id' => $user->id, 'job_posting_id' => $job->id ],
            [
                'cover_letter' => 'I am passionate about backend development and have 5 years of experience.',
                'resume_path' => 'https://example.com/resume/frank-miller.pdf',
                'additional_data' => null,
                'status' => 'pending',
                'applied_at' => now()->subDays(2),
            ]
        );
    }
} 