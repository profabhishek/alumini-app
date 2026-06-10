<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_participants', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('alumni_id');

            // 'member', 'admin' (group admin can rename, add/remove members)
            $table->enum('role', [
                'owner',
                'admin',
                'member'
            ])->default('member');

            // Soft-left: when user leaves we set left_at so history is preserved
            $table->timestamp('left_at')->nullable();

            // Mute notifications until this time (null = not muted)
            $table->timestamp('muted_until')->nullable();

            $table->timestamps();

            $table->foreign('conversation_id')
                  ->references('id')
                  ->on('chat_conversations')
                  ->cascadeOnDelete();

            $table->foreign('alumni_id')
                  ->references('id')
                  ->on('alumni_users')
                  ->cascadeOnDelete();

            // A user can only be in a conversation once (active)
            $table->unique(['conversation_id', 'alumni_id']);

            $table->index('alumni_id');
            $table->index('conversation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_participants');
    }
};