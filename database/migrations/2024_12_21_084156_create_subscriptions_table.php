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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('payment_order_id')->nullable();
            $table->unsignedBigInteger('plan_id');
            $table->decimal('paid_amount', 10, 2)->nullable();
            $table->integer('purchased_credits')->nullable();
            $table->integer('previous_expired_credits')->nullable();
            $table->integer('total_credits')->nullable();
            $table->boolean('payment_status')->nullable();
            $table->date('expiry_date');
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
        
        Schema::dropIfExists('subscriptions');
    }
};
