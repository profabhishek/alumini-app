<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumni_sessions', function (Blueprint $table) {
            $table->string('session_id')->nullable()->unique()->after('alumni_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('alumni_sessions', function (Blueprint $table) {
            $table->dropUnique(['session_id']);
            $table->dropColumn('session_id');
        });
    }
};