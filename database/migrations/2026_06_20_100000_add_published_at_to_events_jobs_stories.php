<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('status');
            }
        });

        Schema::table('jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('jobs', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('status');
            }
        });

        Schema::table('stories', function (Blueprint $table) {
            if (!Schema::hasColumn('stories', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('status');
            }
        });

        // Back-fill existing published rows so the feed works immediately
        DB::statement("UPDATE events SET published_at = updated_at WHERE status = 'published' AND published_at IS NULL");
        DB::statement("UPDATE jobs   SET published_at = updated_at WHERE status = 'published' AND published_at IS NULL");
        DB::statement("UPDATE stories SET published_at = updated_at WHERE status = 'published' AND published_at IS NULL");
    }

    public function down(): void
    {
        Schema::table('events',  fn($t) => $t->dropColumnIfExists('published_at'));
        Schema::table('jobs',    fn($t) => $t->dropColumnIfExists('published_at'));
        Schema::table('stories', fn($t) => $t->dropColumnIfExists('published_at'));
    }
};
