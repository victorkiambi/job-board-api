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
} 