<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Company;
use App\Policies\CompanyPolicy;
use App\Models\JobPosting;
use App\Policies\JobPostingPolicy;
use App\Models\JobApplication;
use App\Policies\JobApplicationPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Company::class => CompanyPolicy::class,
        JobPosting::class => JobPostingPolicy::class,
        JobApplication::class => JobApplicationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
} 