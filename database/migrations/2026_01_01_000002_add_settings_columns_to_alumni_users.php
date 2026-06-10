<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumni_users', function (Blueprint $table) {
            $table->json('email_notifications')->nullable()->after('hide_phone');
            $table->string('appearance')->default('light')->after('email_notifications');
            $table->string('profile_visibility')->default('public')->after('appearance');
        });
    }

    public function down(): void
    {
        Schema::table('alumni_users', function (Blueprint $table) {
            $table->dropColumn(['email_notifications', 'appearance', 'profile_visibility']);
        });
    }
};