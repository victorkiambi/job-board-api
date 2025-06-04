<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'user_type' => 'required|in:job_seeker,company',
            'profile_data' => 'nullable|array',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'user_type' => $validated['user_type'],
            'profile_data' => $validated['profile_data'] ?? null,
        ]);

        $token = $user->createToken($request->userAgent() ?: 'api')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken($request->userAgent() ?: 'api')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }

    // Register a job seeker
    public function registerJobSeeker(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'profile_data' => 'nullable|array',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'user_type' => 'job_seeker',
            'profile_data' => $validated['profile_data'] ?? null,
        ]);

        $token = $user->createToken($request->userAgent() ?: 'api')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    // Register a company
    public function registerCompany(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'profile_data' => 'nullable|array',
            'company.company_name' => 'required|string|max:255',
            'company.description' => 'nullable|string',
            'company.website' => 'nullable|url',
            'company.location' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'user_type' => 'company',
            'profile_data' => $validated['profile_data'] ?? null,
        ]);

        $company = \App\Models\Company::create([
            'company_name' => $validated['company']['company_name'],
            'description' => $validated['company']['description'] ?? null,
            'website' => $validated['company']['website'] ?? null,
            'location' => $validated['company']['location'] ?? null,
        ]);
        $company->users()->attach($user);

        $token = $user->createToken($request->userAgent() ?: 'api')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'company' => new \App\Http\Resources\CompanyResource($company),
            'token' => $token,
        ], 201);
    }
} 