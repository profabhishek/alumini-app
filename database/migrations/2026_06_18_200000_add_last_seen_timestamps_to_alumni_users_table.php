<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumni_users', function (Blueprint $table) {
            if (!Schema::hasColumn('alumni_users', 'applications_last_seen')) {
                $table->timestamp('applications_last_seen')->nullable();
            }
            if (!Schema::hasColumn('alumni_users', 'my_jobs_last_seen')) {
                $table->timestamp('my_jobs_last_seen')->nullable();
            }
            if (!Schema::hasColumn('alumni_users', 'my_stories_last_seen')) {
                $table->timestamp('my_stories_last_seen')->nullable();
            }
            if (!Schema::hasColumn('alumni_users', 'events_regs_seen_at')) {
                $table->timestamp('events_regs_seen_at')->nullable();
            }
            if (!Schema::hasColumn('alumni_users', 'notifications_read_at')) {
                $table->timestamp('notifications_read_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('alumni_users', function (Blueprint $table) {
            $cols = array_filter([
                'applications_last_seen',
                'my_jobs_last_seen',
                'my_stories_last_seen',
                'events_regs_seen_at',
                'notifications_read_at',
            ], fn($c) => Schema::hasColumn('alumni_users', $c));

            if ($cols) {
                $table->dropColumn(array_values($cols));
            }
        });
    }
};
