<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('alumni_id');
            $table->text('body')->nullable();

            // 'text' | 'image' | 'video'
            $table->string('type')->default('text');

            // For shares — points to the original post being shared.
            // The share itself is a row in `posts` with type = original's type
            // and shared_post_id set, body holds the sharer's own caption.
            $table->unsignedBigInteger('shared_post_id')->nullable();

            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->unsignedInteger('shares_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index('alumni_id');
            $table->index('shared_post_id');
            $table->index('created_at');

            $table->foreign('alumni_id')
                ->references('id')->on('alumni_users')
                ->onDelete('cascade');

            $table->foreign('shared_post_id')
                ->references('id')->on('posts')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};