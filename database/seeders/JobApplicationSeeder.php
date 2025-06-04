<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobApplication;
use App\Models\User;
use App\Models\JobPosting;

class JobApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $jobSeekerIds = User::where('user_type', 'job_seeker')->pluck('id');
        $jobPostingIds = JobPosting::pluck('id');

        foreach ($jobSeekerIds as $jobSeekerId) {
            // Each job seeker applies to 3 unique random jobs
            $jobsToApply = $jobPostingIds->random(min(3, $jobPostingIds->count()));
            foreach ($jobsToApply as $jobPostingId) {
                JobApplication::factory()->create([
                    'user_id' => $jobSeekerId,
                    'job_posting_id' => $jobPostingId,
                ]);
            }
        }
    }
} 