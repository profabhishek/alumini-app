<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stories', function (Blueprint $table) {

            $table->id();

            // Creator
            $table->foreignId('created_by')
                  ->constrained('alumni_users')
                  ->cascadeOnDelete();

            $table->string('creator_role');

            // Content
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category');          // e.g. Career, Cultural Exchange, Education
            $table->longText('body');            // rich story content
            $table->string('excerpt', 400)->nullable(); // auto-generated or manual
            $table->string('cover_image')->nullable();

            // Moderation
            // pending → published (admin approves) or rejected
            $table->enum('status', [
                'draft',
                'pending',
                'published',
                'rejected',
            ])->default('pending');

            $table->text('rejection_reason')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};