<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('group_invite_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('community_groups')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('alumni_users')->onDelete('cascade');
            $table->string('token', 64)->unique();
            $table->foreignId('used_by')->nullable()->constrained('alumni_users')->onDelete('set null');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['group_id', 'token']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('group_invite_links');
    }
};
