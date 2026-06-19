<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('group_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('community_groups')->onDelete('cascade');
            $table->foreignId('invited_by')->constrained('alumni_users')->onDelete('cascade');
            $table->foreignId('alumni_id')->constrained('alumni_users')->onDelete('cascade');
            // pending | accepted | declined
            $table->string('status')->default('pending');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            $table->unique(['group_id', 'alumni_id']);
            $table->index(['alumni_id', 'status']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('group_invitations');
    }
};
