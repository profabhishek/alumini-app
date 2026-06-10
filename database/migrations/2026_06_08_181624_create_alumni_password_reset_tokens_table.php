<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumni_password_reset_tokens', function (Blueprint $table) {

            $table->id();

            $table->string('email')->index();

            // Store hashed token — plain token is only ever in the email link
            $table->string('token');

            $table->timestamp('expires_at');

            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni_password_reset_tokens');
    }
};