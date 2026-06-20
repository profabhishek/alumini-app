<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumni_users', function (Blueprint $table) {
            $table->timestamp('feed_cleared_at')->nullable()->after('notifications_read_at');
        });
    }

    public function down(): void
    {
        Schema::table('alumni_users', function (Blueprint $table) {
            $table->dropColumn('feed_cleared_at');
        });
    }
};
