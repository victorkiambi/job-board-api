<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Company;
use App\Policies\CompanyPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected CompanyPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new CompanyPolicy();
    }

    public function test_only_company_users_can_create()
    {
        $companyUser = User::factory()->create(['user_type' => 'company']);
        $jobSeeker = User::factory()->create(['user_type' => 'job_seeker']);

        $this->assertTrue($this->policy->create($companyUser));
        $this->assertFalse($this->policy->create($jobSeeker));
    }

    public function test_only_associated_company_users_can_update()
    {
        $company = Company::factory()->create();
        $companyUser = User::factory()->create(['user_type' => 'company']);
        $otherCompanyUser = User::factory()->create(['user_type' => 'company']);
        $jobSeeker = User::factory()->create(['user_type' => 'job_seeker']);

        $company->users()->attach($companyUser);

        $company->load('users');

        $this->assertTrue($this->policy->update($companyUser, $company));
        $this->assertFalse($this->policy->update($otherCompanyUser, $company));
        $this->assertFalse($this->policy->update($jobSeeker, $company));
    }

    public function test_only_associated_company_users_can_delete()
    {
        $company = Company::factory()->create();
        $companyUser = User::factory()->create(['user_type' => 'company']);
        $otherCompanyUser = User::factory()->create(['user_type' => 'company']);
        $jobSeeker = User::factory()->create(['user_type' => 'job_seeker']);

        $company->users()->attach($companyUser);

        $company->load('users');

        $this->assertTrue($this->policy->delete($companyUser, $company));
        $this->assertFalse($this->policy->delete($otherCompanyUser, $company));
        $this->assertFalse($this->policy->delete($jobSeeker, $company));
    }
} 