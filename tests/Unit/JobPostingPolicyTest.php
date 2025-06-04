<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Company;
use App\Models\JobPosting;
use App\Policies\JobPostingPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobPostingPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected JobPostingPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new JobPostingPolicy();
    }

    public function test_only_company_users_with_company_can_create()
    {
        $company = Company::factory()->create();
        $companyUser = User::factory()->create(['user_type' => 'company']);
        $company->users()->attach($companyUser);
        $companyUser->refresh();

        $otherCompanyUser = User::factory()->create(['user_type' => 'company']);
        $jobSeeker = User::factory()->create(['user_type' => 'job_seeker']);

        $this->assertTrue($this->policy->create($companyUser));
        $this->assertFalse($this->policy->create($otherCompanyUser));
        $this->assertFalse($this->policy->create($jobSeeker));
    }

    public function test_only_associated_company_users_can_update()
    {
        $company = Company::factory()->create();
        $job = JobPosting::factory()->create(['company_id' => $company->id]);
        $companyUser = User::factory()->create(['user_type' => 'company']);
        $company->users()->attach($companyUser);
        $companyUser->refresh();

        $otherCompanyUser = User::factory()->create(['user_type' => 'company']);
        $jobSeeker = User::factory()->create(['user_type' => 'job_seeker']);

        $this->assertTrue($this->policy->update($companyUser, $job));
        $this->assertFalse($this->policy->update($otherCompanyUser, $job));
        $this->assertFalse($this->policy->update($jobSeeker, $job));
    }

    public function test_only_associated_company_users_can_delete()
    {
        $company = Company::factory()->create();
        $job = JobPosting::factory()->create(['company_id' => $company->id]);
        $companyUser = User::factory()->create(['user_type' => 'company']);
        $company->users()->attach($companyUser);
        $companyUser->refresh();

        $otherCompanyUser = User::factory()->create(['user_type' => 'company']);
        $jobSeeker = User::factory()->create(['user_type' => 'job_seeker']);

        $this->assertTrue($this->policy->delete($companyUser, $job));
        $this->assertFalse($this->policy->delete($otherCompanyUser, $job));
        $this->assertFalse($this->policy->delete($jobSeeker, $job));
    }
} 