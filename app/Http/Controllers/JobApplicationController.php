<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\StoreJobApplicationRequest;
use App\Http\Requests\UpdateJobApplicationRequest;
use App\Http\Resources\JobApplicationResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;

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
        Log::info('Job application submitted', [
            'user_id' => $data['user_id'],
            'job_posting_id' => $data['job_posting_id'],
            'applied_at' => now()->toDateTimeString(),
        ]);
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

    public function companyDashboardApplications(Request $request)
    {
        $user = $request->user();
        if ($user->user_type !== 'company') {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $perPage = $request->query('per_page', 15);
        $companyIds = $user->companies()->pluck('companies.id');
        $jobPostingIds = \App\Models\JobPosting::whereIn('company_id', $companyIds)->pluck('id');
        $applications = \App\Models\JobApplication::whereIn('job_posting_id', $jobPostingIds)
            ->with(['user', 'jobPosting'])
            ->paginate($perPage);
        return JobApplicationResource::collection($applications);
    }

    public function jobSeekerDashboardApplications(Request $request)
    {
        $user = $request->user();
        if ($user->user_type !== 'job_seeker') {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $perPage = $request->query('per_page', 15);
        $applications = \App\Models\JobApplication::where('user_id', $user->id)
            ->with('jobPosting')
            ->paginate($perPage);
        return JobApplicationResource::collection($applications);
    }
} 