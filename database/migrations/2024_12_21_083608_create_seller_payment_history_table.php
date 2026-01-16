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
        Schema::create('seller_payment_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id'); 
            $table->string('payment_order_id');
            $table->string('txn_id')->nullable();
            $table->string('gateway');
            $table->decimal('amount',10,2);
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->string('reason')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response')->nullable(); 
            $table->timestamps();
            // Foreign key constraint
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    { 
        
        Schema::dropIfExists('seller_payment_history');
    }
};
