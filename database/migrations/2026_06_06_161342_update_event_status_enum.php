<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    DB::statement("
        ALTER TABLE events
        MODIFY COLUMN status ENUM(
            'pending',
            'draft',
            'published',
            'cancelled',
            'completed',
            'rejected'
        ) NOT NULL DEFAULT 'pending'
    ");
}

public function down()
{
    DB::statement("
        ALTER TABLE events
        MODIFY COLUMN status ENUM(
            'draft',
            'published',
            'cancelled'
        ) NOT NULL DEFAULT 'draft'
    ");
}
};
