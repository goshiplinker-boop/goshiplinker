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
        Schema::create('courier_status_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('courier_id');
            $table->string('courier_status');
            $table->string('shipment_status_code');            
            // Add unique constraint for courier_id and shipment_status_code
            $table->unique(['courier_id', 'courier_status'], 'courier_status_unique');            
            // Add foreign key constraint
            $table->foreign('courier_id')->references('id')->on('couriers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courier_status_mappings');
    }
};
