<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_group_join_requests', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('alumni_id');   // who is requesting

            $table->enum('status', ['pending', 'accepted', 'rejected'])
                  ->default('pending');

            // Which admin acted on the request
            $table->unsignedBigInteger('acted_by')->nullable();
            $table->timestamp('acted_at')->nullable();

            $table->timestamps();

            $table->foreign('conversation_id')
                  ->references('id')
                  ->on('chat_conversations')
                  ->cascadeOnDelete();

            $table->foreign('alumni_id')
                  ->references('id')
                  ->on('alumni_users')
                  ->cascadeOnDelete();

            $table->foreign('acted_by')
                  ->references('id')
                  ->on('alumni_users')
                  ->nullOnDelete();

            // Only one pending request per person per group at a time
            $table->unique(['conversation_id', 'alumni_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_group_join_requests');
    }
};