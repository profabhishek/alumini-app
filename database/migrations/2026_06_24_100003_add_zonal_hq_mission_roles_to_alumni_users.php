<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL doesn't support ALTER COLUMN for ENUMs easily on PXC.
        // Use MODIFY COLUMN to expand the enum to include the two new roles.
        DB::statement("ALTER TABLE alumni_users MODIFY COLUMN role ENUM('alumni','moderator','admin','super_admin','zonal_hq','mission') NOT NULL DEFAULT 'alumni'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE alumni_users MODIFY COLUMN role ENUM('alumni','moderator','admin','super_admin') NOT NULL DEFAULT 'alumni'");
    }
};
