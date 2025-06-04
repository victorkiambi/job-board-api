<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\StoreJobApplicationRequest;
use App\Http\Requests\UpdateJobApplicationRequest;
use App\Http\Resources\JobApplicationResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class JobApplicationController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(JobApplication::class, 'job_application');
    }

    public function index()
    {
        $applications = JobApplication::all();
        return JobApplicationResource::collection($applications);
    }

    public function store(StoreJobApplicationRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $application = JobApplication::create($data);
        return new JobApplicationResource($application);
    }

    public function show(JobApplication $jobApplication)
    {
        return new JobApplicationResource($jobApplication);
    }

    public function update(UpdateJobApplicationRequest $request, JobApplication $jobApplication)
    {
        $jobApplication->update($request->validated());
        return new JobApplicationResource($jobApplication);
    }

    public function destroy(JobApplication $jobApplication)
    {
        $jobApplication->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
} 