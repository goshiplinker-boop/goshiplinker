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
        Schema::create('order_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index()->nullable();
            $table->unsignedBigInteger('order_id')->index()->nullable();
            $table->string('vendor_order_id')->index()->nullable();
            $table->string('type')->index(); // e.g., info, error, success
            $table->json('payload')->nullable(); // to store request data
            $table->json('response')->nullable(); 
            $table->boolean('status')->index()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_logs');
    }
};
