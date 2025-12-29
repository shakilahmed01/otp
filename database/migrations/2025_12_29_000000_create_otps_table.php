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
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('channel')->default('email'); // email or sms
            $table->string('target')->nullable(); // email or phone value
            $table->string('token', 10)->index();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('used')->default(false);
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'channel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
