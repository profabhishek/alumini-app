<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {

            $table->id();

            $table->foreignId('job_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedBigInteger('alumni_id');

            $table->string('full_name');

            $table->string('email');

            $table->string('phone', 30)->nullable();

            $table->string('resume')->nullable();

            $table->longText('cover_letter')->nullable();

            $table->enum('status', [
                'submitted',
                'shortlisted',
                'rejected',
                'hired'
            ])->default('submitted');

            $table->timestamps();

            $table->unique([
                'job_id',
                'alumni_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
