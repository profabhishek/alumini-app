<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentor_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('alumni_user_id')->unique();
            $table->text('bio');
            $table->unsignedTinyInteger('experience_years')->default(0);
            $table->string('expertise')->nullable();          // Short tagline
            $table->string('availability')->nullable();       // e.g. "Weekends", "Evenings"
            $table->unsignedTinyInteger('max_mentees')->default(5);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamps();

            $table->foreign('alumni_user_id')->references('id')->on('alumni_users')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('alumni_users')->onDelete('set null');
        });

        // Pivot: mentor_profile <-> mentor_category
        Schema::create('mentor_profile_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('mentor_profile_id');
            $table->unsignedBigInteger('mentor_category_id');
            $table->primary(['mentor_profile_id', 'mentor_category_id']);
            $table->foreign('mentor_profile_id')->references('id')->on('mentor_profiles')->onDelete('cascade');
            $table->foreign('mentor_category_id')->references('id')->on('mentor_categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentor_profile_categories');
        Schema::dropIfExists('mentor_profiles');
    }
};
