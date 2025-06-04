<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'description' => $this->description,
            'website' => $this->website,
            'location' => $this->location,
            'logo_path' => $this->logo_path,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 