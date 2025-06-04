<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Company;

class AuthRegistrationTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function a_job_seeker_can_register_successfully()
    {
        $payload = [
            'name' => 'Alice Jobseeker',
            'email' => 'alice@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'profile_data' => [
                'phone' => '+1234567890',
                'skills' => ['PHP', 'Laravel'],
                'resume_url' => 'https://example.com/resume.pdf',
            ],
        ];

        $response = $this->postJson('/api/v1/register/job-seeker', $payload);
        $response->assertCreated();
        $response->assertJsonStructure([
            'user' => [
                'id', 'name', 'email', 'user_type', 'profile_data', 'created_at', 'updated_at'
            ],
            'token',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'alice@example.com',
            'user_type' => 'job_seeker',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function a_company_can_register_successfully()
    {
        $payload = [
            'name' => 'Bob Recruiter',
            'email' => 'bob@company.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'profile_data' => [
                'phone' => '+1987654321',
                'contact_person' => 'Bob Recruiter',
            ],
            'company' => [
                'company_name' => 'Acme Corp',
                'description' => 'A leading tech company',
                'website' => 'https://acme.com',
                'location' => 'New York',
            ],
        ];

        $response = $this->postJson('/api/v1/register/company', $payload);
        $response->assertCreated();
        $response->assertJsonStructure([
            'user' => [
                'id', 'name', 'email', 'user_type', 'profile_data', 'created_at', 'updated_at'
            ],
            'company' => [
                'id', 'company_name', 'description', 'website', 'location', 'created_at', 'updated_at'
            ],
            'token',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'bob@company.com',
            'user_type' => 'company',
        ]);
        $this->assertDatabaseHas('companies', [
            'company_name' => 'Acme Corp',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function registration_requires_required_fields()
    {
        $response = $this->postJson('/api/v1/register/job-seeker', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function company_registration_requires_company_data()
    {
        $payload = [
            'name' => 'Bob Recruiter',
            'email' => 'bob@company.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'profile_data' => [
                'phone' => '+1987654321',
                'contact_person' => 'Bob Recruiter',
            ],
            // 'company' => [ ... ] missing
        ];
        $response = $this->postJson('/api/v1/register/company', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['company.company_name']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function registration_fails_with_duplicate_email()
    {
        User::factory()->create([
            'email' => 'alice@example.com',
            'user_type' => 'job_seeker',
        ]);
        $payload = [
            'name' => 'Alice Jobseeker',
            'email' => 'alice@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
        $response = $this->postJson('/api/v1/register/job-seeker', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }
} 