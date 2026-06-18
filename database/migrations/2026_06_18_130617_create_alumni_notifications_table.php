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
        Schema::create('alumni_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipient_id'); 
            $table->unsignedBigInteger('actor_id');    
            $table->string('type');                     
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('comment_id')->nullable();
            $table->text('preview')->nullable();      
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index('recipient_id');
            $table->index(['recipient_id', 'is_read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni_notifications');
    }
};
