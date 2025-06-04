<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\JobPostingResource;
use App\Http\Resources\UserResource;

class JobApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'job_posting_id' => $this->job_posting_id,
            'user' => new UserResource($this->whenLoaded('user') ?? $this->user),
            'job_posting' => new JobPostingResource($this->whenLoaded('jobPosting') ?? $this->jobPosting),
            'cover_letter' => $this->cover_letter,
            'resume_path' => $this->resume_path,
            'additional_data' => $this->additional_data,
            'status' => $this->status,
            'applied_at' => $this->applied_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 