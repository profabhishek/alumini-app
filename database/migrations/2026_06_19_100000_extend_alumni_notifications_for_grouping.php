<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumni_notifications', function (Blueprint $table) {
            // Make post_id nullable (group-join notifications have no post)
            $table->unsignedBigInteger('post_id')->nullable()->change();

            // For Facebook-style grouping: how many actors triggered this notification
            if (!Schema::hasColumn('alumni_notifications', 'actor_count')) {
                $table->unsignedInteger('actor_count')->default(1)->after('is_read');
            }
            // Comma-separated first-names of up to 2 actors e.g. "Amit, Rahul"
            if (!Schema::hasColumn('alumni_notifications', 'actor_names')) {
                $table->string('actor_names')->nullable()->after('actor_count');
            }
            // group_id so we can label "in GroupName"
            if (!Schema::hasColumn('alumni_notifications', 'group_id')) {
                $table->unsignedBigInteger('group_id')->nullable()->after('actor_names');
            }
        });
    }

    public function down(): void
    {
        Schema::table('alumni_notifications', function (Blueprint $table) {
            $table->dropColumn(array_filter(
                ['actor_count', 'actor_names', 'group_id'],
                fn($c) => Schema::hasColumn('alumni_notifications', $c)
            ));
        });
    }
};
