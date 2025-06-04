<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('location');
            $table->decimal('salary_min', 10, 2)->nullable();
            $table->decimal('salary_max', 10, 2)->nullable();
            $table->enum('job_type', ['full_time', 'part_time', 'contract', 'internship']);
            $table->enum('status', ['active', 'inactive', 'filled'])->default('active');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
            $table->index(['location', 'job_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
}; 