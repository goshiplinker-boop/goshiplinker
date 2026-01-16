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
        Schema::create('payment_mappings', function (Blueprint $table) {
            $table->id();  
            $table->unsignedBigInteger('company_id');     
            $table->unsignedBigInteger('channel_id');                 
            $table->string('payment_mode')->nullable();    
            $table->string('gateway_name');
            $table->boolean('status')->default(0); // or use enum if needed
            $table->timestamps();
            // Foreign key constraints
            $table->foreign('channel_id')->references('id')->on('channels');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->unique(['channel_id', 'company_id', 'gateway_name'], 'unique_channel_company_gateway');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_mappings');
    }
};
