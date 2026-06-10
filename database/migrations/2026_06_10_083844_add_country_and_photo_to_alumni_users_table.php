<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumni_users', function (Blueprint $table) {
            $table->string('country')->nullable()->after('institute');
            $table->string('photo')->nullable()->after('country');
        });
    }

    public function down(): void
    {
        Schema::table('alumni_users', function (Blueprint $table) {
            $table->dropColumn(['country', 'photo']);
        });
    }
};