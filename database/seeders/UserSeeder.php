<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [ 'email' => 'alice@acmetech.com' ],
            [
                'name' => 'Alice Johnson',
                'email' => 'alice@acmetech.com',
                'password' => Hash::make('password123'),
                'user_type' => 'company',
                'profile_data' => [
                    'phone' => '+1234567890',
                    'contact_person' => 'Alice Johnson',
                ],
            ]
        );

        User::updateOrCreate(
            [ 'email' => 'frank.miller@email.com' ],
            [
                'name' => 'Frank Miller',
                'email' => 'frank.miller@email.com',
                'password' => Hash::make('password123'),
                'user_type' => 'job_seeker',
                'profile_data' => [
                    'phone' => '+15551234567',
                    'skills' => ['PHP', 'Laravel', 'REST APIs'],
                    'resume_url' => 'https://example.com/resume/frank-miller.pdf',
                ],
            ]
        );
    }
} 