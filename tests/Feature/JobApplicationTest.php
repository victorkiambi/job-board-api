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
        $job = JobPosting::factory()->create([
            'company_id' => $company->id,
            'status' => 'active',
            'expires_at' => now()->addDays(10),
        ]);

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
            'job_posting_id' => 999999,
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_job_listing_filter_by_location()
    {
        $company = Company::factory()->create();
        $job1 = JobPosting::factory()->create(['company_id' => $company->id, 'location' => 'New York']);
        $job2 = JobPosting::factory()->create(['company_id' => $company->id, 'location' => 'San Francisco']);
        $job3 = JobPosting::factory()->create(['company_id' => $company->id, 'location' => 'Yorkshire']);
        $this->actingAs(User::factory()->create(['user_type' => 'job_seeker']));
        $response = $this->getJson('/api/v1/job-postings?location=York');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($job1->id));
        $this->assertTrue($ids->contains($job3->id));
        $this->assertFalse($ids->contains($job2->id));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_job_listing_filter_by_job_type()
    {
        $company = Company::factory()->create();
        $job1 = JobPosting::factory()->create(['company_id' => $company->id, 'job_type' => 'full_time']);
        $job2 = JobPosting::factory()->create(['company_id' => $company->id, 'job_type' => 'part_time']);
        $this->actingAs(User::factory()->create(['user_type' => 'job_seeker']));
        $response = $this->getJson('/api/v1/job-postings?job_type=full_time');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($job1->id));
        $this->assertFalse($ids->contains($job2->id));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_job_listing_filter_by_salary_min()
    {
        $company = Company::factory()->create();
        $job1 = JobPosting::factory()->create(['company_id' => $company->id, 'salary_max' => 50000]);
        $job2 = JobPosting::factory()->create(['company_id' => $company->id, 'salary_max' => 80000]);
        $this->actingAs(User::factory()->create(['user_type' => 'job_seeker']));
        $response = $this->getJson('/api/v1/job-postings?salary_min=60000');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertFalse($ids->contains($job1->id));
        $this->assertTrue($ids->contains($job2->id));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_job_listing_filter_by_salary_max()
    {
        $company = Company::factory()->create();
        $job1 = JobPosting::factory()->create(['company_id' => $company->id, 'salary_min' => 30000]);
        $job2 = JobPosting::factory()->create(['company_id' => $company->id, 'salary_min' => 70000]);
        $this->actingAs(User::factory()->create(['user_type' => 'job_seeker']));
        $response = $this->getJson('/api/v1/job-postings?salary_max=40000');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($job1->id));
        $this->assertFalse($ids->contains($job2->id));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_job_listing_filter_combined()
    {
        $company = Company::factory()->create();
        $job1 = JobPosting::factory()->create(['company_id' => $company->id, 'location' => 'Remote', 'job_type' => 'contract', 'salary_min' => 40000, 'salary_max' => 60000]);
        $job2 = JobPosting::factory()->create(['company_id' => $company->id, 'location' => 'Remote', 'job_type' => 'full_time', 'salary_min' => 30000, 'salary_max' => 50000]);
        $job3 = JobPosting::factory()->create(['company_id' => $company->id, 'location' => 'Onsite', 'job_type' => 'contract', 'salary_min' => 40000, 'salary_max' => 60000]);
        $this->actingAs(User::factory()->create(['user_type' => 'job_seeker']));
        $response = $this->getJson('/api/v1/job-postings?location=Remote&job_type=contract&salary_min=50000&salary_max=60000');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($job1->id));
        $this->assertFalse($ids->contains($job2->id));
        $this->assertFalse($ids->contains($job3->id));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function only_owner_can_update_job_application()
    {
        $company = Company::factory()->create();
        $job = JobPosting::factory()->create(['company_id' => $company->id]);
        $owner = User::factory()->create(['user_type' => 'job_seeker']);
        $otherUser = User::factory()->create(['user_type' => 'job_seeker']);
        $application = JobApplication::factory()->create([
            'user_id' => $owner->id,
            'job_posting_id' => $job->id,
            'cover_letter' => 'Original cover letter',
        ]);

        // Owner can update
        $this->actingAs($owner);
        $response = $this->putJson('/api/v1/job-applications/' . $application->id, [
            'cover_letter' => 'Updated by owner',
        ]);
        $response->assertOk();
        $this->assertEquals('Updated by owner', $response->json('data.cover_letter'));

        // Other user cannot update
        $this->actingAs($otherUser);
        $response = $this->putJson('/api/v1/job-applications/' . $application->id, [
            'cover_letter' => 'Malicious update',
        ]);
        $response->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function only_owner_can_delete_job_application()
    {
        $company = Company::factory()->create();
        $job = JobPosting::factory()->create(['company_id' => $company->id]);
        $owner = User::factory()->create(['user_type' => 'job_seeker']);
        $otherUser = User::factory()->create(['user_type' => 'job_seeker']);
        $application = JobApplication::factory()->create([
            'user_id' => $owner->id,
            'job_posting_id' => $job->id,
        ]);

        // Other user cannot delete
        $this->actingAs($otherUser);
        $response = $this->deleteJson('/api/v1/job-applications/' . $application->id);
        $response->assertForbidden();
        $this->assertDatabaseHas('job_applications', [
            'id' => $application->id,
        ]);

        // Owner can delete
        $this->actingAs($owner);
        $response = $this->deleteJson('/api/v1/job-applications/' . $application->id);
        $response->assertNoContent();
        $this->assertDatabaseMissing('job_applications', [
            'id' => $application->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cannot_apply_to_inactive_filled_or_expired_job()
    {
        $company = Company::factory()->create();
        $jobSeeker = User::factory()->create(['user_type' => 'job_seeker']);
        $this->actingAs($jobSeeker);

        // Inactive job
        $inactiveJob = JobPosting::factory()->create([
            'company_id' => $company->id,
            'status' => 'inactive',
            'expires_at' => now()->addDays(10),
        ]);
        $payload = [
            'job_posting_id' => $inactiveJob->id,
        ];
        $response = $this->postJson('/api/v1/job-applications', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['job_posting_id']);
        $this->assertStringContainsString('not open', $response->json('errors.job_posting_id.0'));

        // Filled job
        $filledJob = JobPosting::factory()->create([
            'company_id' => $company->id,
            'status' => 'filled',
            'expires_at' => now()->addDays(10),
        ]);
        $payload = [
            'job_posting_id' => $filledJob->id,
        ];
        $response = $this->postJson('/api/v1/job-applications', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['job_posting_id']);
        $this->assertStringContainsString('not open', $response->json('errors.job_posting_id.0'));

        // Expired job
        $expiredJob = JobPosting::factory()->create([
            'company_id' => $company->id,
            'status' => 'active',
            'expires_at' => now()->subDay(),
        ]);
        $payload = [
            'job_posting_id' => $expiredJob->id,
        ];
        $response = $this->postJson('/api/v1/job-applications', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['job_posting_id']);
        $this->assertStringContainsString('expired', $response->json('errors.job_posting_id.0'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function only_job_seekers_can_apply_for_jobs()
    {
        $company = Company::factory()->create();
        $job = JobPosting::factory()->create([
            'company_id' => $company->id,
            'status' => 'active',
            'expires_at' => now()->addDays(10),
        ]);
        $companyUser = User::factory()->create(['user_type' => 'company']);
        $this->actingAs($companyUser);
        $payload = [
            'job_posting_id' => $job->id,
            'cover_letter' => 'Trying to apply as a company user.',
        ];
        $response = $this->postJson('/api/v1/job-applications', $payload);
        $response->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function users_cannot_view_update_delete_applications_they_do_not_own()
    {
        $company = Company::factory()->create();
        $job = JobPosting::factory()->create([
            'company_id' => $company->id,
            'status' => 'active',
            'expires_at' => now()->addDays(10),
        ]);
        $owner = User::factory()->create(['user_type' => 'job_seeker']);
        $otherUser = User::factory()->create(['user_type' => 'job_seeker']);
        $application = JobApplication::factory()->create([
            'user_id' => $owner->id,
            'job_posting_id' => $job->id,
            'cover_letter' => 'Owner application',
        ]);

        // Other user cannot view
        $this->actingAs($otherUser);
        $response = $this->getJson('/api/v1/job-applications/' . $application->id);
        $response->assertForbidden();

        // Other user cannot update
        $response = $this->putJson('/api/v1/job-applications/' . $application->id, [
            'cover_letter' => 'Malicious update',
        ]);
        $response->assertForbidden();

        // Other user cannot delete
        $response = $this->deleteJson('/api/v1/job-applications/' . $application->id);
        $response->assertForbidden();
    }

    public function test_company_user_can_view_applications_for_their_company_postings()
    {
        $company = \App\Models\Company::factory()->create(['company_name' => 'Acme Tech Solutions']);
        $alice = \App\Models\User::factory()->create([
            'email' => 'alice@acmetech.com',
            'user_type' => 'company',
            'password' => bcrypt('password123'),
        ]);
        $company->users()->attach($alice);

        $posting = \App\Models\JobPosting::factory()->create([
            'company_id' => $company->id,
            'title' => 'Backend Developer',
        ]);

        $jobSeeker = \App\Models\User::factory()->create([
            'email' => 'frank.miller@email.com',
            'user_type' => 'job_seeker',
        ]);
        \App\Models\JobApplication::factory()->create([
            'user_id' => $jobSeeker->id,
            'job_posting_id' => $posting->id,
        ]);

        // Login as Alice
        $response = $this->postJson('/api/v1/login', [
            'email' => 'alice@acmetech.com',
            'password' => 'password123',
        ]);
        $response->assertOk();
        $token = $response->json('token') ?? $response->json('access_token');
        $this->assertNotEmpty($token, 'Login did not return a token');

        // Get applications as Alice
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/dashboard/company/applications');
        $response->assertOk();
        $data = $response->json('data');
        $this->assertNotEmpty($data, 'No applications returned for company user');

        $found = false;
        foreach ($data as $app) {
            if (
                isset($app['user']['email']) && $app['user']['email'] === 'frank.miller@email.com' &&
                isset($app['job_posting']['title']) && $app['job_posting']['title'] === 'Backend Developer'
            ) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Expected application for Backend Developer by Frank Miller not found');
    }
} 