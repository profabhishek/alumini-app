<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumni_data', function (Blueprint $table) {
            $table->id();
            $table->string('legacy_user_id')->nullable()->index();
            $table->string('alumni_code')->nullable()->index();
            $table->string('name')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->text('profile_image')->nullable();
            $table->text('linkedin_url')->nullable();
            $table->text('facebook_url')->nullable();
            $table->string('current_company')->nullable();
            $table->string('current_designation')->nullable();
            $table->string('current_city')->nullable();
            $table->string('current_country')->nullable();
            $table->string('course')->nullable();
            $table->string('branch')->nullable();
            $table->string('campus')->nullable();
            $table->string('institute')->nullable();
            $table->string('level_of_study')->nullable();
            $table->string('joining_year')->nullable();
            $table->string('graduation_year')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('address_city')->nullable();
            $table->string('address_state')->nullable();
            $table->string('address_country')->nullable();
            $table->string('address_pincode')->nullable();
            $table->timestamp('record_created_at')->nullable();
            $table->timestamp('record_updated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni_data');
    }
};