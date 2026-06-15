<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('community_groups')->onDelete('cascade');
            $table->foreignId('alumni_id')->constrained('alumni_users')->onDelete('cascade');

            // 'member' | 'moderator' | 'admin' — group-level role, separate
            // from the platform-wide alumni_role.
            $table->enum('role', ['member', 'moderator', 'admin'])->default('member');

            // 'pending' | 'approved' — join requests sit as pending until
            // an admin/moderator of the group approves them. The creator
            // is inserted directly as approved+admin.
            $table->enum('status', ['pending', 'approved'])->default('pending');

            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['group_id', 'alumni_id']);
            $table->index(['group_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_group_members');
    }
};