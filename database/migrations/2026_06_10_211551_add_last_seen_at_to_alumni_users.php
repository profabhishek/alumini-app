<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumni_users', function (Blueprint $table) {
            // Nullable — null means never seen / account just created
            $table->timestamp('last_seen_at')
                  ->nullable()
                  ->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('alumni_users', function (Blueprint $table) {
            $table->dropColumn('last_seen_at');
        });
    }
};