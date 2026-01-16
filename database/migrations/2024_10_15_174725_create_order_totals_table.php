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
        Schema::create('order_totals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('title'); // Title of the total (e.g., Subtotal, Shipping, Tax)
            $table->string('code'); // Code to identify the type (e.g., 'subtotal', 'shipping', 'tax')
            $table->decimal('value', 10, 2); // The value of the total
            $table->integer('sort_order'); // Sort order for display
            $table->timestamps();
            // Foreign key relation to orders table
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_totals');
    }
};
