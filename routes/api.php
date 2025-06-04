<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\JobPostingController;
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
    Route::middleware('auth:sanctum')->apiResource('companies', CompanyController::class);
    Route::middleware('auth:sanctum')->apiResource('job-postings', JobPostingController::class);
    Route::middleware('auth:sanctum')->apiResource('job-applications', JobApplicationController::class);
    Route::middleware('auth:sanctum')->get('/dashboard/company/applications', [JobApplicationController::class, 'companyDashboardApplications']);
    Route::middleware('auth:sanctum')->get('/dashboard/job-seeker/applications', [JobApplicationController::class, 'jobSeekerDashboardApplications']);
});
