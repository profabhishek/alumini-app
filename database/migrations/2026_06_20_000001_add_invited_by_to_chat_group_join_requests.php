<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_group_join_requests', function (Blueprint $table) {
            // When not null, this request was initiated by an admin (an invitation)
            // When null, the user themselves requested to join
            $table->unsignedBigInteger('invited_by')->nullable()->after('status');

            $table->foreign('invited_by')
                  ->references('id')
                  ->on('alumni_users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('chat_group_join_requests', function (Blueprint $table) {
            $table->dropForeign(['invited_by']);
            $table->dropColumn('invited_by');
        });
    }
};
