<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumni_users', function (Blueprint $table) {
            $table->text('bio')->nullable()->after('photo');
            $table->string('linkedin_url')->nullable()->after('bio');
            $table->string('twitter_url')->nullable()->after('linkedin_url');
            $table->string('facebook_url')->nullable()->after('twitter_url');
            $table->string('website_url')->nullable()->after('facebook_url');
            $table->string('current_job_title')->nullable()->after('website_url');
            $table->string('current_company')->nullable()->after('current_job_title');
            $table->string('current_city')->nullable()->after('current_company');
            $table->boolean('hide_email')->default(false)->after('current_city');
            $table->boolean('hide_phone')->default(false)->after('hide_email');
        });
    }

    public function down(): void
    {
        Schema::table('alumni_users', function (Blueprint $table) {
            $table->dropColumn([
                'bio', 'linkedin_url', 'twitter_url', 'facebook_url',
                'website_url', 'current_job_title', 'current_company',
                'current_city', 'hide_email', 'hide_phone',
            ]);
        });
    }
};