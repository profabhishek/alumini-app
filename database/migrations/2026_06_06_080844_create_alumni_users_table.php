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
    Schema::create('alumni_users', function (Blueprint $table) {

        $table->id();

        $table->string('full_name');

        $table->string('batch_name');

        $table->string('phone');

        $table->string('email')->unique();

        $table->string('department');

        $table->year('passing_year');

        $table->string('roll_number');

        $table->string('attachment');

        $table->date('birth_date');

        $table->string('gender');

        $table->string('institute');

        $table->string('password');

        $table->boolean('is_approved')
              ->default(false);

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni_users');
    }
};
