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
        Schema::create('pincode_master', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('courier_id');
            $table->string('pincode', 10);
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('route_code', 50)->nullable();
            $table->boolean('forward_pickup')->default(0);
            $table->boolean('forward_delivery')->default(0);
            $table->boolean('reverse_pickup')->default(0);
            $table->boolean('cod')->default(0);
            $table->boolean('prepaid')->default(0);
            $table->boolean('status')->default(1);
            $table->timestamps();
            // Indexes
            $table->index(['company_id', 'courier_id', 'pincode'], 'pm_company_courier_pincode_idx');

            $table->index(
                ['company_id', 'courier_id', 'pincode', 'forward_pickup', 'status', 'cod', 'prepaid'],
                'pm_pickup_idx'
            );

            $table->index(
                ['company_id', 'courier_id', 'pincode', 'forward_delivery', 'status', 'cod', 'prepaid'],
                'pm_delivery_idx'
            );

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pincode_master');
    }
};
