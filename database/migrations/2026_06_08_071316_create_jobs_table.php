<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('created_by')
                  ->constrained('alumni_users')
                  ->cascadeOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->string('company_name');
            $table->string('location')->nullable();

            $table->enum('job_type', [
                'Full-Time',
                'Part-Time',
                'Contract',
                'Internship',
            ])->default('Full-Time');

            $table->enum('work_mode', [
                'Remote',
                'On-site',
                'Hybrid',
            ])->default('On-site');

            $table->unsignedInteger('salary_min')->nullable();
            $table->unsignedInteger('salary_max')->nullable();

            $table->text('description');
            $table->text('requirements')->nullable();

            $table->date('application_deadline')->nullable();
            $table->string('application_link')->nullable();

            $table->string('banner_image')->nullable();

            $table->enum('status', [
                'pending',
                'published',
                'rejected',
            ])->default('pending');

            $table->string('rejection_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};