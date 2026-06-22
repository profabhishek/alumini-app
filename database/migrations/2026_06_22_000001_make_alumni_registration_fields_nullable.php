<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ICCR Alumni (yes-path) users don't supply batch_name, phone,
     * department, passing_year, gender or institute at registration.
     * Make those columns nullable so both paths can save cleanly.
     */
    public function up(): void
    {
        Schema::table('alumni_users', function (Blueprint $table) {
            $table->string('batch_name')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->string('department')->nullable()->change();
            $table->integer('passing_year')->unsigned()->nullable()->change();
            $table->string('gender')->nullable()->change();
            $table->string('institute')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('alumni_users', function (Blueprint $table) {
            $table->string('batch_name')->nullable(false)->change();
            $table->string('phone')->nullable(false)->change();
            $table->string('department')->nullable(false)->change();
            $table->integer('passing_year')->unsigned()->nullable(false)->change();
            $table->string('gender')->nullable(false)->change();
            $table->string('institute')->nullable(false)->change();
        });
    }
};
