<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comment_likes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('comment_id');
            $table->unsignedBigInteger('alumni_id');
            $table->timestamps();

            $table->unique(['comment_id', 'alumni_id']);
            $table->index('alumni_id');

            $table->foreign('comment_id')
                ->references('id')->on('post_comments')
                ->onDelete('cascade');

            $table->foreign('alumni_id')
                ->references('id')->on('alumni_users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_likes');
    }
};