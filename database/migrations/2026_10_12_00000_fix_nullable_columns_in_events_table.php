<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->unsignedInteger('total_seats')->nullable()->change();
            $table->date('registration_deadline')->nullable()->change();
            $table->string('banner_image')->nullable()->change();
            $table->string('end_date')->nullable()->change();
            $table->string('end_time')->nullable()->change();
            $table->string('location')->nullable()->change();
            $table->decimal('ticket_price', 10, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->unsignedInteger('total_seats')->nullable(false)->change();
            $table->date('registration_deadline')->nullable(false)->change();
            $table->string('banner_image')->nullable(false)->change();
            $table->date('end_date')->nullable(false)->change();
            $table->string('end_time')->nullable(false)->change();
            $table->string('location')->nullable(false)->change();
        });
    }
};