<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumni_users', function (Blueprint $table) {
            if (!Schema::hasColumn('alumni_users', 'nationality')) {
                $table->string('nationality')->nullable()->after('institute');
            }
            if (!Schema::hasColumn('alumni_users', 'is_iccr_alumni')) {
                $table->boolean('is_iccr_alumni')->nullable()->after('nationality');
            }
            if (!Schema::hasColumn('alumni_users', 'current_position')) {
                $table->string('current_position')->nullable()->after('is_iccr_alumni');
            }
            // make birth_date nullable
            $table->date('birth_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('alumni_users', function (Blueprint $table) {
            $table->dropColumn(['nationality', 'is_iccr_alumni', 'current_position']);
            $table->date('birth_date')->nullable(false)->change();
        });
    }
};
