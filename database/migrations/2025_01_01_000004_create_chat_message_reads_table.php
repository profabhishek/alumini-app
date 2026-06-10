<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tracks the last message each participant has read in each conversation.
        // One row per participant per conversation — we update it rather than
        // insert a row for every single message, keeping the table small.
        Schema::create('chat_message_reads', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('alumni_id');
            $table->unsignedBigInteger('last_read_message_id');

            $table->timestamps();

            $table->foreign('conversation_id')
                  ->references('id')
                  ->on('chat_conversations')
                  ->cascadeOnDelete();

            $table->foreign('alumni_id')
                  ->references('id')
                  ->on('alumni_users')
                  ->cascadeOnDelete();

            $table->foreign('last_read_message_id')
                  ->references('id')
                  ->on('chat_messages')
                  ->cascadeOnDelete();

            $table->unique(['conversation_id', 'alumni_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_message_reads');
    }
};