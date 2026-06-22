<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumni_data', function (Blueprint $table) {
            // Stores the source system's user classification.
            // Known values from ICCR CSV export: alumni, student, faculty, official
            $table->string('user_type')->nullable()->after('alumni_code');
        });
    }

    public function down(): void
    {
        Schema::table('alumni_data', function (Blueprint $table) {
            $table->dropColumn('user_type');
        });
    }
};
