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
        Schema::create('order_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('product_name');
            $table->string('sku');
            $table->decimal('unit_price', 10, 2); // Unit price of the product
            $table->integer('quantity');
            $table->decimal('discount', 10, 2)->default(0); // Discount applied to the product
            $table->decimal('shipping', 10, 2)->default(0); // Shipping cost for the product
            $table->string('hsn')->nullable(); // HSN code
            $table->decimal('tax_rate', 5, 2)->default(0); // Tax rate in percentage
            $table->tinyInteger('tax_type')->default(0)->comment('0 = inclusive, 1 = exclusive');
            $table->string('tax_name')->nullable();
            $table->decimal('tax_amount', 10, 2)->default(0); // Calculated tax amount
            $table->decimal('total_price', 10, 2); // Total price after discount, tax, and shipping
            $table->string('line_item_id')->nullable();
            $table->unsignedBigInteger('fulfillment_id')->nullable()->index();
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
        Schema::dropIfExists('order_products');
    }
};
