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
        Schema::create('order_packages', function (Blueprint $table) {
            $table->id();            
            $table->unsignedBigInteger('order_id');
            $table->string('package_code')->nullable();
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('breadth', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('dead_weight', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->index(['order_id', 'package_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_packages');
    }
};
