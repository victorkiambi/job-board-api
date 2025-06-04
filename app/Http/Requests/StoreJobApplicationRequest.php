<?php

namespace App\Http\Requests;

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
} 