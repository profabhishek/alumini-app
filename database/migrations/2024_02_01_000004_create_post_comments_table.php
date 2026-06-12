<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('alumni_id');

            // Null = top-level comment. Set = reply to a top-level comment.
            // One level only — replies cannot have their own parent set to a reply.
            $table->unsignedBigInteger('parent_id')->nullable();

            $table->text('body');
            $table->unsignedInteger('likes_count')->default(0);

            // Replies count only tracked on top-level comments
            $table->unsignedInteger('replies_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index('post_id');
            $table->index('parent_id');
            $table->index('alumni_id');

            $table->foreign('post_id')
                ->references('id')->on('posts')
                ->onDelete('cascade');

            $table->foreign('alumni_id')
                ->references('id')->on('alumni_users')
                ->onDelete('cascade');

            $table->foreign('parent_id')
                ->references('id')->on('post_comments')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_comments');
    }
};