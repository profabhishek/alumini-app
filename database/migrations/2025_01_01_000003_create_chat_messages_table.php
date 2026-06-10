<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('sender_id');

            // Message types
            $table->enum('type', [
                'text',
                'image',
                'video',
                'file',
                'pdf',
                'system',   // "X joined the group", "X left the group"
            ])->default('text');

            $table->text('body')->nullable();           // text content / system message
            $table->string('file_path')->nullable();    // storage path
            $table->string('file_name')->nullable();    // original filename
            $table->string('file_mime')->nullable();    // MIME type
            $table->unsignedBigInteger('file_size')->nullable(); // bytes

            // Reply threading (optional, WhatsApp-style)
            $table->unsignedBigInteger('reply_to_id')->nullable();

            // Soft-delete (sender can delete their own message)
            $table->softDeletes();

            $table->timestamps();

            $table->foreign('conversation_id')
                  ->references('id')
                  ->on('chat_conversations')
                  ->cascadeOnDelete();

            $table->foreign('sender_id')
                  ->references('id')
                  ->on('alumni_users')
                  ->cascadeOnDelete();

            $table->foreign('reply_to_id')
                  ->references('id')
                  ->on('chat_messages')
                  ->nullOnDelete();

            $table->index(['conversation_id', 'created_at']);
            $table->index('sender_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};