<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'description',
        'website',
        'location',
        'logo_path',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'company_user');
    }

    public function jobPostings()
    {
        return $this->hasMany(JobPosting::class);
    }
} 