<?php

namespace App\Policies;

use App\Models\User;
use App\Models\JobPosting;
use App\Models\Company;

class JobPostingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, JobPosting $jobPosting): bool
    {
        return true;
    }

    public function create(User $user, Company $company): bool
    {
        return $user->user_type === 'company'
            && $user->companies()->where('companies.id', $company->id)->exists();
    }

    public function update(User $user, JobPosting $jobPosting): bool
    {
        return $user->user_type === 'company' && $user->companies()->where('companies.id', $jobPosting->company_id)->exists();
    }

    public function delete(User $user, JobPosting $jobPosting): bool
    {
        return $user->user_type === 'company' && $user->companies()->where('companies.id', $jobPosting->company_id)->exists();
    }
} 