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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('tran_id')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('BDT');
            $table->string('status')->default('pending'); // pending, success, failed, cancelled
            $table->string('payment_method')->nullable();
            $table->string('bank_tran_id')->nullable();
            $table->string('card_type')->nullable();
            $table->string('card_no')->nullable();
            $table->text('product_name')->nullable();
            $table->text('product_category')->nullable();
            $table->string('cus_name')->nullable();
            $table->string('cus_email')->nullable();
            $table->string('cus_phone')->nullable();
            $table->text('cus_address')->nullable();
            $table->text('cus_city')->nullable();
            $table->string('cus_country')->nullable();
            $table->string('cus_postcode')->nullable();
            $table->text('success_url')->nullable();
            $table->text('fail_url')->nullable();
            $table->text('cancel_url')->nullable();
            $table->string('ipn_url')->nullable();
            $table->text('ssl_response')->nullable(); // Store full response from SSL Commerce
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->index('tran_id');
            $table->index('status');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
