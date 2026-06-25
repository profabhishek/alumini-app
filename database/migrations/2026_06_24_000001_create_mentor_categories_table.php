<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentor_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color', 20)->default('#e8640c');
            $table->string('icon_svg', 500)->nullable(); // SVG path data
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed default categories
        DB::table('mentor_categories')->insert([
            ['name' => 'Classical Dance',   'slug' => 'classical-dance',   'color' => '#9b59b6', 'description' => 'Bharatanatyam, Kathak, Odissi and other classical dance forms', 'sort_order' => 1,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Yoga & Wellness',   'slug' => 'yoga-wellness',     'color' => '#27ae60', 'description' => 'Yoga, meditation and holistic wellness practices',              'sort_order' => 2,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Music',             'slug' => 'music',             'color' => '#e67e22', 'description' => 'Classical, folk and contemporary Indian music',                 'sort_order' => 3,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Visual Arts',       'slug' => 'visual-arts',       'color' => '#e74c3c', 'description' => 'Painting, sculpture, photography and digital arts',             'sort_order' => 4,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Language & Culture','slug' => 'language-culture',  'color' => '#2980b9', 'description' => 'Hindi, Sanskrit, regional languages and cultural studies',      'sort_order' => 5,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Career & Business', 'slug' => 'career-business',   'color' => '#16a085', 'description' => 'Professional development, entrepreneurship and career guidance', 'sort_order' => 6,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Research & Academia','slug' => 'research-academia','color' => '#8e44ad', 'description' => 'Academic research, scholarships and higher education',           'sort_order' => 7,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Technology',        'slug' => 'technology',        'color' => '#2c3e50', 'description' => 'Software development, AI, data science and emerging tech',       'sort_order' => 8,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Theatre & Drama',   'slug' => 'theatre-drama',     'color' => '#c0392b', 'description' => 'Stage performance, dramaturgy and scriptwriting',               'sort_order' => 9,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Culinary Arts',     'slug' => 'culinary-arts',     'color' => '#f39c12', 'description' => 'Indian cuisine, food culture and culinary traditions',           'sort_order' => 10, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('mentor_categories');
    }
};
