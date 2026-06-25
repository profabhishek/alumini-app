<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentee_connections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mentor_profile_id');
            $table->unsignedBigInteger('mentee_id');         // alumni_users.id
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending');
            $table->text('message')->nullable();             // mentee intro message
            $table->text('mentor_note')->nullable();         // mentor's response note
            $table->timestamp('connected_at')->nullable();
            $table->timestamps();

            $table->unique(['mentor_profile_id', 'mentee_id']); // one request per pair
            $table->foreign('mentor_profile_id')->references('id')->on('mentor_profiles')->onDelete('cascade');
            $table->foreign('mentee_id')->references('id')->on('alumni_users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentee_connections');
    }
};
