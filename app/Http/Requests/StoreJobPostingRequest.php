<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Company;

class StoreJobPostingRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (!$this->has('company_id')) {
            return true;
        }
        
        $company = Company::find($this->input('company_id'));
        return $company && $this->user()->user_type === 'company' 
            && $this->user()->companies()->where('companies.id', $company->id)->exists();
    
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|exists:companies,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'salary_min' => 'nullable|numeric',
            'salary_max' => 'nullable|numeric',
            'job_type' => 'required|in:full_time,part_time,contract,internship',
            'status' => 'nullable|in:active,inactive,filled',
            'expires_at' => 'nullable|date',
        ];
    }
} 