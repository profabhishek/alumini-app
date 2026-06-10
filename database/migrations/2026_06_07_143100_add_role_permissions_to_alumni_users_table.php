<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('alumni_users', function (Blueprint $table) {
            $table->enum('role', [
                'alumni',
                'moderator', 
                'admin',
                'super_admin'
            ])->default('alumni')->change();

            // Granular permissions for moderators
            $table->json('permissions')->nullable()->after('role');
            // e.g. {"approve_events": true, "manage_categories": false}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumni_users', function (Blueprint $table) {
            //
        });
    }
};
