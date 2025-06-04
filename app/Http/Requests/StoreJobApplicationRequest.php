<?php

namespace App\Http\Requests;

use App\Models\JobPosting;
use Illuminate\Foundation\Http\FormRequest;

class StoreJobApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'job_posting_id' => 'required|exists:job_postings,id',
            'cover_letter' => 'nullable|string',
            'resume_path' => 'nullable|string|max:255',
            'additional_data' => 'nullable|array',
            'status' => 'nullable|in:pending,reviewed,shortlisted,rejected,hired',
            'applied_at' => 'nullable|date',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            $jobPostingId = $this->input('job_posting_id');
            if ($user && $jobPostingId) {
                $exists = \App\Models\JobApplication::where('user_id', $user->id)
                    ->where('job_posting_id', $jobPostingId)
                    ->exists();
                if ($exists) {
                    $validator->errors()->add('job_posting_id', 'You have already applied to this job.');
                }
                $job = JobPosting::find($jobPostingId);
                if ($job) {
                    if ($job->status !== 'active') {
                        $validator->errors()->add('job_posting_id', 'You cannot apply to a job that is not open.');
                    }
                    if ($job->expires_at && $job->expires_at < now()) {
                        $validator->errors()->add('job_posting_id', 'You cannot apply to an expired job.');
                    }
                }
            }
        });
    }
}
