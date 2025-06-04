<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\JobPosting;

class JobPostingTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function any_authenticated_user_can_list_job_postings()
    {
        $company = Company::factory()->create();
        $job1 = JobPosting::factory()->create(['company_id' => $company->id, 'location' => 'Remote']);
        $job2 = JobPosting::factory()->create(['company_id' => $company->id, 'location' => 'Onsite']);
        $user = User::factory()->create(['user_type' => 'job_seeker']);
        $this->actingAs($user);
        $response = $this->getJson('/api/v1/job-postings');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($job1->id));
        $this->assertTrue($ids->contains($job2->id));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function job_postings_can_be_filtered_by_location_job_type_and_salary()
    {
        $company = Company::factory()->create();
        $job1 = JobPosting::factory()->create(['company_id' => $company->id, 'location' => 'Remote', 'job_type' => 'full_time', 'salary_min' => 50000, 'salary_max' => 100000]);
        $job2 = JobPosting::factory()->create(['company_id' => $company->id, 'location' => 'Onsite', 'job_type' => 'part_time', 'salary_min' => 30000, 'salary_max' => 50000]);
        $user = User::factory()->create(['user_type' => 'job_seeker']);
        $this->actingAs($user);
        $response = $this->getJson('/api/v1/job-postings?location=Remote&job_type=full_time&salary_min=60000&salary_max=100000');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($job1->id));
        $this->assertFalse($ids->contains($job2->id));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function any_authenticated_user_can_view_a_job_posting()
    {
        $company = Company::factory()->create();
        $job = JobPosting::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create(['user_type' => 'job_seeker']);
        $this->actingAs($user);
        $response = $this->getJson('/api/v1/job-postings/' . $job->id);
        $response->assertOk();
        $response->assertJsonFragment(['id' => $job->id]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function company_user_can_create_job_posting_for_their_company()
    {
        $company = Company::factory()->create();
        $companyUser = User::factory()->create(['user_type' => 'company']);
        $company->users()->attach($companyUser);
        $this->actingAs($companyUser);
        $payload = [
            'company_id' => $company->id,
            'title' => 'Backend Developer',
            'description' => 'Develop APIs',
            'location' => 'Remote',
            'salary_min' => 60000,
            'salary_max' => 120000,
            'job_type' => 'full_time',
        ];
        $response = $this->postJson('/api/v1/job-postings', $payload);
        $response->assertCreated();
        $this->assertDatabaseHas('job_postings', [
            'company_id' => $company->id,
            'title' => 'Backend Developer',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function job_seeker_cannot_create_job_posting()
    {
        $company = Company::factory()->create();
        $jobSeeker = User::factory()->create(['user_type' => 'job_seeker']);
        $this->actingAs($jobSeeker);
        $payload = [
            'company_id' => $company->id,
            'title' => 'Backend Developer',
            'description' => 'Develop APIs',
            'location' => 'Remote',
            'salary_min' => 60000,
            'salary_max' => 120000,
            'job_type' => 'full_time',
        ];
        $response = $this->postJson('/api/v1/job-postings', $payload);
        $response->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function company_user_cannot_create_job_posting_for_other_company()
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        $companyUser = User::factory()->create(['user_type' => 'company']);
        $company1->users()->attach($companyUser);
        $this->actingAs($companyUser);
        $payload = [
            'company_id' => $company2->id,
            'title' => 'Backend Developer',
            'description' => 'Develop APIs',
            'location' => 'Remote',
            'salary_min' => 60000,
            'salary_max' => 120000,
            'job_type' => 'full_time',
        ];
        $response = $this->postJson('/api/v1/job-postings', $payload);
        $response->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function company_user_can_update_their_own_job_posting()
    {
        $company = Company::factory()->create();
        $companyUser = User::factory()->create(['user_type' => 'company']);
        $company->users()->attach($companyUser);
        $job = JobPosting::factory()->create(['company_id' => $company->id, 'title' => 'Old Title']);
        $this->actingAs($companyUser);
        $payload = [
            'title' => 'New Title',
        ];
        $response = $this->putJson('/api/v1/job-postings/' . $job->id, $payload);
        $response->assertOk();
        $this->assertDatabaseHas('job_postings', [
            'id' => $job->id,
            'title' => 'New Title',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function company_user_cannot_update_job_posting_of_other_company()
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        $companyUser = User::factory()->create(['user_type' => 'company']);
        $company1->users()->attach($companyUser);
        $job = JobPosting::factory()->create(['company_id' => $company2->id, 'title' => 'Old Title']);
        $this->actingAs($companyUser);
        $payload = [
            'title' => 'New Title',
        ];
        $response = $this->putJson('/api/v1/job-postings/' . $job->id, $payload);
        $response->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function job_seeker_cannot_update_any_job_posting()
    {
        $company = Company::factory()->create();
        $job = JobPosting::factory()->create(['company_id' => $company->id, 'title' => 'Old Title']);
        $jobSeeker = User::factory()->create(['user_type' => 'job_seeker']);
        $this->actingAs($jobSeeker);
        $payload = [
            'title' => 'New Title',
        ];
        $response = $this->putJson('/api/v1/job-postings/' . $job->id, $payload);
        $response->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function company_user_can_delete_their_own_job_posting()
    {
        $company = Company::factory()->create();
        $companyUser = User::factory()->create(['user_type' => 'company']);
        $company->users()->attach($companyUser);
        $job = JobPosting::factory()->create(['company_id' => $company->id]);
        $this->actingAs($companyUser);
        $response = $this->deleteJson('/api/v1/job-postings/' . $job->id);
        $response->assertNoContent();
        $this->assertDatabaseMissing('job_postings', [
            'id' => $job->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function company_user_cannot_delete_job_posting_of_other_company()
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        $companyUser = User::factory()->create(['user_type' => 'company']);
        $company1->users()->attach($companyUser);
        $job = JobPosting::factory()->create(['company_id' => $company2->id]);
        $this->actingAs($companyUser);
        $response = $this->deleteJson('/api/v1/job-postings/' . $job->id);
        $response->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function job_seeker_cannot_delete_any_job_posting()
    {
        $company = Company::factory()->create();
        $job = JobPosting::factory()->create(['company_id' => $company->id]);
        $jobSeeker = User::factory()->create(['user_type' => 'job_seeker']);
        $this->actingAs($jobSeeker);
        $response = $this->deleteJson('/api/v1/job-postings/' . $job->id);
        $response->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cannot_create_job_posting_with_invalid_data()
    {
        $company = Company::factory()->create();
        $companyUser = User::factory()->create(['user_type' => 'company']);
        $company->users()->attach($companyUser);
        $this->actingAs($companyUser);
        $payload = [
            // missing required fields
        ];
        $response = $this->postJson('/api/v1/job-postings', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['company_id', 'title', 'description', 'location', 'job_type']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cannot_update_job_posting_with_invalid_data()
    {
        $company = Company::factory()->create();
        $companyUser = User::factory()->create(['user_type' => 'company']);
        $company->users()->attach($companyUser);
        $job = JobPosting::factory()->create(['company_id' => $company->id]);
        $this->actingAs($companyUser);
        $payload = [
            'salary_min' => 'not-a-number',
        ];
        $response = $this->putJson('/api/v1/job-postings/' . $job->id, $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['salary_min']);
    }
} 