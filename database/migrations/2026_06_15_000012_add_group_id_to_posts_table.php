<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // NULL = main site feed post. Non-null = belongs to that
            // community group's feed instead.
            $table->foreignId('group_id')
                ->nullable()
                ->after('alumni_id')
                ->constrained('community_groups')
                ->onDelete('cascade');

            $table->index(['group_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropIndex(['group_id', 'created_at']);
            $table->dropColumn('group_id');
        });
    }
};