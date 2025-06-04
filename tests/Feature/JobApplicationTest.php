<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\JobPosting;
use App\Models\JobApplication;
use App\Models\Company;

class JobApplicationTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function a_user_cannot_apply_to_the_same_job_twice()
    {
        $user = User::factory()->create(['user_type' => 'job_seeker']);
        $company = Company::factory()->create();
        $job = JobPosting::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user);

        $payload = [
            'job_posting_id' => $job->id,
            'cover_letter' => 'I am interested in this job.',
        ];

        // First application should succeed
        $response1 = $this->postJson('/api/v1/job-applications', $payload);
        $response1->assertCreated();
        $this->assertDatabaseHas('job_applications', [
            'user_id' => $user->id,
            'job_posting_id' => $job->id,
        ]);

        // Second application should fail
        $response2 = $this->postJson('/api/v1/job-applications', $payload);
        $response2->assertStatus(422);
        $response2->assertJsonValidationErrors(['job_posting_id']);
        $this->assertEquals(
            'You have already applied to this job.',
            $response2->json('errors.job_posting_id.0')
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cannot_apply_without_job_posting_id()
    {
        $user = User::factory()->create(['user_type' => 'job_seeker']);
        $this->actingAs($user);

        $payload = [
            // 'job_posting_id' => missing
            'cover_letter' => 'Missing job posting id.',
        ];

        $response = $this->postJson('/api/v1/job-applications', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['job_posting_id']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cannot_apply_to_nonexistent_job_posting()
    {
        $user = User::factory()->create(['user_type' => 'job_seeker']);
        $this->actingAs($user);

        $payload = [
            'job_posting_id' => 999999, // unlikely to exist
            'cover_letter' => 'Non-existent job posting.',
        ];

        $response = $this->postJson('/api/v1/job-applications', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['job_posting_id']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_company_can_view_all_applications_for_their_job_posts()
    {
        $company = Company::factory()->create();
        $companyUser = User::factory()->create(['user_type' => 'company']);
        $company->users()->attach($companyUser);
        $job1 = JobPosting::factory()->create(['company_id' => $company->id]);
        $job2 = JobPosting::factory()->create(['company_id' => $company->id]);
        $jobSeeker1 = User::factory()->create(['user_type' => 'job_seeker']);
        $jobSeeker2 = User::factory()->create(['user_type' => 'job_seeker']);
        $app1 = JobApplication::factory()->create(['user_id' => $jobSeeker1->id, 'job_posting_id' => $job1->id]);
        $app2 = JobApplication::factory()->create(['user_id' => $jobSeeker2->id, 'job_posting_id' => $job2->id]);
        $this->actingAs($companyUser);
        $response = $this->getJson('/api/v1/dashboard/company/applications');
        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['id' => $app1->id]);
        $response->assertJsonFragment(['id' => $app2->id]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_job_seeker_can_view_their_applications()
    {
        $company = Company::factory()->create();
        $job = JobPosting::factory()->create(['company_id' => $company->id]);
        $jobSeeker = User::factory()->create(['user_type' => 'job_seeker']);
        $otherJobSeeker = User::factory()->create(['user_type' => 'job_seeker']);
        $app1 = JobApplication::factory()->create(['user_id' => $jobSeeker->id, 'job_posting_id' => $job->id]);
        $app2 = JobApplication::factory()->create(['user_id' => $otherJobSeeker->id, 'job_posting_id' => $job->id]);
        $this->actingAs($jobSeeker);
        $response = $this->getJson('/api/v1/dashboard/job-seeker/applications');
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $app1->id]);
        $response->assertJsonMissing(['id' => $app2->id]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_company_dashboard_forbidden_for_job_seeker()
    {
        $jobSeeker = User::factory()->create(['user_type' => 'job_seeker']);
        $this->actingAs($jobSeeker);
        $response = $this->getJson('/api/v1/dashboard/company/applications');
        $response->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_job_seeker_dashboard_forbidden_for_company()
    {
        $company = Company::factory()->create();
        $companyUser = User::factory()->create(['user_type' => 'company']);
        $company->users()->attach($companyUser);
        $this->actingAs($companyUser);
        $response = $this->getJson('/api/v1/dashboard/job-seeker/applications');
        $response->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_company_can_view_all_applications_for_their_job_posts_with_pagination()
    {
        $company = Company::factory()->create();
        $companyUser = User::factory()->create(['user_type' => 'company']);
        $company->users()->attach($companyUser);
        $job1 = JobPosting::factory()->create(['company_id' => $company->id]);
        $job2 = JobPosting::factory()->create(['company_id' => $company->id]);
        $job3 = JobPosting::factory()->create(['company_id' => $company->id]);
        $jobSeeker1 = User::factory()->create(['user_type' => 'job_seeker']);
        $jobSeeker2 = User::factory()->create(['user_type' => 'job_seeker']);
        $jobSeeker3 = User::factory()->create(['user_type' => 'job_seeker']);
        $app1 = JobApplication::factory()->create(['user_id' => $jobSeeker1->id, 'job_posting_id' => $job1->id]);
        $app2 = JobApplication::factory()->create(['user_id' => $jobSeeker2->id, 'job_posting_id' => $job2->id]);
        $app3 = JobApplication::factory()->create(['user_id' => $jobSeeker3->id, 'job_posting_id' => $job3->id]);
        $this->actingAs($companyUser);
        $response = $this->getJson('/api/v1/dashboard/company/applications?per_page=2');
        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJsonStructure(['data', 'links', 'meta']);
        $this->assertEquals(2, $response->json('meta.per_page'));
        $this->assertEquals(2, count($response->json('data')));
        // Page 2
        $response2 = $this->getJson('/api/v1/dashboard/company/applications?per_page=2&page=2');
        $response2->assertOk();
        $response2->assertJsonCount(1, 'data');
        $response2->assertJsonStructure(['data', 'links', 'meta']);
        $this->assertEquals(2, $response2->json('meta.per_page'));
        $this->assertEquals(1, count($response2->json('data')));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_job_seeker_can_view_their_applications_with_pagination()
    {
        $company = Company::factory()->create();
        $job1 = JobPosting::factory()->create(['company_id' => $company->id]);
        $job2 = JobPosting::factory()->create(['company_id' => $company->id]);
        $jobSeeker = User::factory()->create(['user_type' => 'job_seeker']);
        $otherJobSeeker = User::factory()->create(['user_type' => 'job_seeker']);
        $app1 = JobApplication::factory()->create(['user_id' => $jobSeeker->id, 'job_posting_id' => $job1->id]);
        $app2 = JobApplication::factory()->create(['user_id' => $jobSeeker->id, 'job_posting_id' => $job2->id]);
        $app3 = JobApplication::factory()->create(['user_id' => $otherJobSeeker->id, 'job_posting_id' => $job1->id]);
        $this->actingAs($jobSeeker);
        $response = $this->getJson('/api/v1/dashboard/job-seeker/applications?per_page=1');
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonStructure(['data', 'links', 'meta']);
        $this->assertEquals(1, $response->json('meta.per_page'));
        $this->assertEquals(1, count($response->json('data')));
        // Page 2
        $response2 = $this->getJson('/api/v1/dashboard/job-seeker/applications?per_page=1&page=2');
        $response2->assertOk();
        $response2->assertJsonCount(1, 'data');
        $response2->assertJsonStructure(['data', 'links', 'meta']);
        $this->assertEquals(1, $response2->json('meta.per_page'));
        $this->assertEquals(1, count($response2->json('data')));
    }
} 