<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\StoreJobPostingRequest;
use App\Http\Requests\UpdateJobPostingRequest;
use App\Http\Resources\JobPostingResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class JobPostingController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(JobPosting::class, 'job_posting');
    }

    public function index()
    {
        $jobs = JobPosting::all();
        return JobPostingResource::collection($jobs);
    }

    public function store(StoreJobPostingRequest $request)
    {
        $job = JobPosting::create($request->validated());
        return new JobPostingResource($job);
    }

    public function show(JobPosting $jobPosting)
    {
        return new JobPostingResource($jobPosting);
    }

    public function update(UpdateJobPostingRequest $request, JobPosting $jobPosting)
    {
        $jobPosting->update($request->validated());
        return new JobPostingResource($jobPosting);
    }

    public function destroy(JobPosting $jobPosting)
    {
        $jobPosting->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
} 