<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');

            // 'image' | 'video'
            $table->string('type');

            $table->string('file_path');
            $table->string('file_name')->nullable();
            $table->string('file_mime')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            // For videos — optional generated thumbnail (future use)
            $table->string('thumbnail_path')->nullable();

            $table->unsignedSmallInteger('position')->default(0);

            $table->timestamps();

            $table->index('post_id');

            $table->foreign('post_id')
                ->references('id')->on('posts')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_media');
    }
};