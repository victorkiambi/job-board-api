<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::prefix('v1')->group(function () {
    Route::post('/register/job-seeker', [AuthController::class, 'registerJobSeeker']);
    Route::post('/register/company', [AuthController::class, 'registerCompany']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });
    Route::middleware('auth:sanctum')->apiResource('companies', \App\Http\Controllers\CompanyController::class);
    Route::middleware('auth:sanctum')->apiResource('job-postings', \App\Http\Controllers\JobPostingController::class);
    Route::middleware(['auth:sanctum', 'throttle:job-applications,5,1'])->post('/job-applications', [\App\Http\Controllers\JobApplicationController::class, 'store']);
    Route::middleware('auth:sanctum')->apiResource('job-applications', \App\Http\Controllers\JobApplicationController::class, ['except' => ['store']]);
    Route::middleware('auth:sanctum')->get('/dashboard/company/applications', [\App\Http\Controllers\JobApplicationController::class, 'companyDashboardApplications']);
    Route::middleware('auth:sanctum')->get('/dashboard/job-seeker/applications', [\App\Http\Controllers\JobApplicationController::class, 'jobSeekerDashboardApplications']);
});
