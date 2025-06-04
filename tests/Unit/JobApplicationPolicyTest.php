<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Company;
use App\Models\JobPosting;
use App\Models\JobApplication;
use App\Policies\JobApplicationPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobApplicationPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected JobApplicationPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new JobApplicationPolicy();
    }

    public function test_only_job_seekers_can_create()
    {
        $jobSeeker = User::factory()->create(['user_type' => 'job_seeker']);
        $companyUser = User::factory()->create(['user_type' => 'company']);

        $this->assertTrue($this->policy->create($jobSeeker));
        $this->assertFalse($this->policy->create($companyUser));
    }

    public function test_only_applicant_can_update()
    {
        $jobSeeker = User::factory()->create(['user_type' => 'job_seeker']);
        $otherJobSeeker = User::factory()->create(['user_type' => 'job_seeker']);
        $company = Company::factory()->create();
        $jobPosting = JobPosting::factory()->create(['company_id' => $company->id]);
        $application = JobApplication::factory()->create([
            'user_id' => $jobSeeker->id,
            'job_posting_id' => $jobPosting->id,
        ]);
        $companyUser = User::factory()->create(['user_type' => 'company']);
        $company->users()->attach($companyUser);

        $this->assertTrue($this->policy->update($jobSeeker, $application));
        $this->assertFalse($this->policy->update($otherJobSeeker, $application));
        $this->assertFalse($this->policy->update($companyUser, $application));
    }

    public function test_only_applicant_can_delete()
    {
        $jobSeeker = User::factory()->create(['user_type' => 'job_seeker']);
        $otherJobSeeker = User::factory()->create(['user_type' => 'job_seeker']);
        $company = Company::factory()->create();
        $jobPosting = JobPosting::factory()->create(['company_id' => $company->id]);
        $application = JobApplication::factory()->create([
            'user_id' => $jobSeeker->id,
            'job_posting_id' => $jobPosting->id,
        ]);
        $companyUser = User::factory()->create(['user_type' => 'company']);
        $company->users()->attach($companyUser);

        $this->assertTrue($this->policy->delete($jobSeeker, $application));
        $this->assertFalse($this->policy->delete($otherJobSeeker, $application));
        $this->assertFalse($this->policy->delete($companyUser, $application));
    }
} 