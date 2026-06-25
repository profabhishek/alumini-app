<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->string('country')->nullable()->after('title');
            $table->string('event_name')->nullable()->after('country');
            $table->date('event_date')->nullable()->after('event_name');
        });
    }

    public function down(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->dropColumn(['country', 'event_name', 'event_date']);
        });
    }
};
