<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\SoftDeletes;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('events', function (Blueprint $table) {

        $table->id();

        // creator
        $table->foreignId('created_by');

        $table->string('creator_role');

        // basic details
        $table->string('title');
        $table->string('slug')->unique();

        $table->string('category');
        $table->string('event_mode');

        $table->string('location')->nullable();

        $table->date('start_date');
        $table->date('end_date')->nullable();

        $table->time('start_time');
        $table->time('end_time')->nullable();

        $table->longText('description');

        // registration
        $table->enum('event_type', [
            'Free',
            'Paid'
        ])->default('Free');

        $table->decimal('ticket_price', 10, 2)
            ->nullable();

        $table->integer('total_seats')
            ->default(0);

        $table->date('registration_deadline')
            ->nullable();

        $table->boolean('registration_required')
            ->default(true);

        // media
        $table->string('banner_image')
            ->nullable();

        // event status
        $table->enum('status', [
            'draft',
            'published',
            'cancelled'
        ])->default('draft');

        $table->softDeletes();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
