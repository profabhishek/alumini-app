<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();

            // 'direct' = 1-to-1 DM, 'group' = group chat
            $table->enum('type', ['direct', 'group'])->default('direct');

            // Group-only fields
            $table->string('name')->nullable();
            $table->string('avatar')->nullable();         // group icon path
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); // group creator

            // Invite link token (groups only)
            $table->string('invite_token', 64)->nullable()->unique();
            $table->boolean('allow_join_via_link')->default(false);
            $table->unsignedInteger('member_limit')->default(500);
            // Soft-delete so message history is preserved
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')
                  ->references('id')
                  ->on('alumni_users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_conversations');
    }
};