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
        Schema::create('sellers_couriers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('courier_id');
            $table->unsignedBigInteger('company_id');
            $table->boolean('seller_courier_status')->default(0); // 0 = inactive, 1 = active
            $table->boolean('main_courier_status')->default(0);
            $table->timestamps();
            $table->unique(['courier_id', 'company_id'], 'unique_courier_company');
            $table->foreign('courier_id')
                ->references('courier_id')        // column in courier_settings
                ->on('courier_settings')
                ->onDelete('cascade');

            $table->foreign('company_id')
                ->references('id')                // id in companies table
                ->on('companies')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sellers_couriers');
    }
};
