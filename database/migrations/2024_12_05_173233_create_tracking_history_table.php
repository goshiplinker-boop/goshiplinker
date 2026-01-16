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
        Schema::create('tracking_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('courier_id');
            $table->string('tracking_number');
            $table->string('current_shipment_status');
            $table->string('current_shipment_status_code');
            $table->timestamp('current_shipment_status_date')->nullable();
            $table->string('current_shipment_location')->nullable();            
            $table->timestamps(); // created_at and updated_at
            // Foreign key constraints
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_history');
    }
};
