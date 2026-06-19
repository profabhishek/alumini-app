<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumni_users', function (Blueprint $table) {
            if (!Schema::hasColumn('alumni_users', 'pending_users_last_seen')) {
                $table->timestamp('pending_users_last_seen')->nullable()->after('my_stories_last_seen');
            }
            if (!Schema::hasColumn('alumni_users', 'newsletter_last_seen')) {
                $table->timestamp('newsletter_last_seen')->nullable()->after('pending_users_last_seen');
            }
        });
    }

    public function down(): void
    {
        Schema::table('alumni_users', function (Blueprint $table) {
            $table->dropColumn(['pending_users_last_seen', 'newsletter_last_seen']);
        });
    }
};
