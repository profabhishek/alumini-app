<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumni_data', function (Blueprint $table) {
            // When the alumni registered on the source platform (AlmaConnect).
            // Distinct from record_updated_at (last profile update).
            $table->timestamp('registration_date')->nullable()->after('record_updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('alumni_data', function (Blueprint $table) {
            $table->dropColumn('registration_date');
        });
    }
};
