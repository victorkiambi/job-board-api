<?php

namespace App\Policies;

use App\Models\User;
use App\Models\JobApplication;

class JobApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, JobApplication $jobApplication): bool
    {
        // Applicant or company user associated with the job's company
        return $user->id === $jobApplication->user_id ||
            ($user->user_type === 'company' && $user->companies()->where('companies.id', $jobApplication->jobPosting->company_id)->exists());
    }

    public function create(User $user): bool
    {
        return $user->user_type === 'job_seeker';
    }

    public function update(User $user, JobApplication $jobApplication): bool
    {
        // Only the applicant can update
        return $user->id === $jobApplication->user_id;
    }

    public function delete(User $user, JobApplication $jobApplication): bool
    {
        // Only the applicant can delete
        return $user->id === $jobApplication->user_id;
    }
} 