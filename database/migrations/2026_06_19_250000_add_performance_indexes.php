<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── alumni_users ───────────────────────────────────────────────────
        Schema::table('alumni_users', function (Blueprint $table) {
            // Filtered by role (admin/moderator lookups)
            if (!$this->hasIndex('alumni_users', 'alumni_users_role_index')) {
                $table->index('role');
            }
            // Filtered by approval status constantly
            if (!$this->hasIndex('alumni_users', 'alumni_users_is_approved_index')) {
                $table->index('is_approved');
            }
            // Combined filter used in admin pending list
            if (!$this->hasIndex('alumni_users', 'alumni_users_is_approved_created_at_index')) {
                $table->index(['is_approved', 'created_at']);
            }
        });

        // ── events ─────────────────────────────────────────────────────────
        Schema::table('events', function (Blueprint $table) {
            // WHERE status = 'published' on public listing
            if (!$this->hasIndex('events', 'events_status_index')) {
                $table->index('status');
            }
            // WHERE created_by = ? on my-events
            if (!$this->hasIndex('events', 'events_created_by_index')) {
                $table->index('created_by');
            }
            // Composite for public listing: WHERE status = ? ORDER BY start_date
            if (!$this->hasIndex('events', 'events_status_start_date_index')) {
                $table->index(['status', 'start_date']);
            }
            // Composite for admin/moderator: WHERE created_by = ? AND status = ?
            if (!$this->hasIndex('events', 'events_created_by_status_index')) {
                $table->index(['created_by', 'status']);
            }
        });

        // ── event_registrations ────────────────────────────────────────────
        Schema::table('event_registrations', function (Blueprint $table) {
            // WHERE user_id = ? for "registered event IDs" lookup on every page
            if (!$this->hasIndex('event_registrations', 'event_registrations_user_id_index')) {
                $table->index('user_id');
            }
            if (!$this->hasIndex('event_registrations', 'event_registrations_created_at_index')) {
                $table->index('created_at');
            }
        });

        // ── stories ────────────────────────────────────────────────────────
        Schema::table('stories', function (Blueprint $table) {
            if (!$this->hasIndex('stories', 'stories_status_index')) {
                $table->index('status');
            }
            if (!$this->hasIndex('stories', 'stories_created_by_index')) {
                $table->index('created_by');
            }
            if (!$this->hasIndex('stories', 'stories_status_created_at_index')) {
                $table->index(['status', 'created_at']);
            }
        });

        // ── jobs ───────────────────────────────────────────────────────────
        Schema::table('jobs', function (Blueprint $table) {
            if (!$this->hasIndex('jobs', 'jobs_status_index')) {
                $table->index('status');
            }
            if (!$this->hasIndex('jobs', 'jobs_created_by_index')) {
                $table->index('created_by');
            }
            if (!$this->hasIndex('jobs', 'jobs_status_created_at_index')) {
                $table->index(['status', 'created_at']);
            }
        });

        // ── job_applications ───────────────────────────────────────────────
        Schema::table('job_applications', function (Blueprint $table) {
            // WHERE alumni_id = ? for "my applications" — composite unique covers (job_id, alumni_id) but not alumni_id alone
            if (!$this->hasIndex('job_applications', 'job_applications_alumni_id_index')) {
                $table->index('alumni_id');
            }
            if (!$this->hasIndex('job_applications', 'job_applications_alumni_id_status_index')) {
                $table->index(['alumni_id', 'status']);
            }
        });

        // ── news ───────────────────────────────────────────────────────────
        Schema::table('news', function (Blueprint $table) {
            if (!$this->hasIndex('news', 'news_status_published_at_index')) {
                $table->index(['status', 'published_at']);
            }
        });

        // ── notices ────────────────────────────────────────────────────────
        Schema::table('notices', function (Blueprint $table) {
            if (!$this->hasIndex('notices', 'notices_status_published_at_index')) {
                $table->index(['status', 'published_at']);
            }
        });

        // ── gallery_items ──────────────────────────────────────────────────
        Schema::table('gallery_items', function (Blueprint $table) {
            if (!$this->hasIndex('gallery_items', 'gallery_items_status_sort_order_index')) {
                $table->index(['status', 'sort_order']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('alumni_users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['is_approved']);
            $table->dropIndex(['is_approved', 'created_at']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['status', 'start_date']);
            $table->dropIndex(['created_by', 'status']);
        });

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('stories', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['status', 'created_at']);
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['status', 'created_at']);
        });

        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropIndex(['alumni_id']);
            $table->dropIndex(['alumni_id', 'status']);
        });

        Schema::table('news', function (Blueprint $table) {
            $table->dropIndex(['status', 'published_at']);
        });

        Schema::table('notices', function (Blueprint $table) {
            $table->dropIndex(['status', 'published_at']);
        });

        Schema::table('gallery_items', function (Blueprint $table) {
            $table->dropIndex(['status', 'sort_order']);
        });
    }

    /**
     * Check if an index already exists to avoid duplicate index errors.
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = \Illuminate\Support\Facades\DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        );
        return !empty($indexes);
    }
};
