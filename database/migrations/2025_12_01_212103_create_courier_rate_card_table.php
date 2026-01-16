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
        Schema::create('courier_rate_card', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('courier_id');
            $table->enum('zone_name', ['A', 'B', 'C', 'D', 'E', 'F']);

            $table->decimal('weight_slab_kg', 8, 2);

            $table->decimal('base_freight_forward', 10, 2);
            $table->decimal('additional_freight', 10, 2);
            $table->decimal('rto_freight', 10, 2);

            $table->decimal('cod_charge', 10, 2);
            $table->decimal('cod_percentage', 5, 2);

            $table->string('delivery_sla', 50);
            $table->boolean('cod_allowed')->default(true);
            $table->integer('sort_order')->nullable();
            $table->boolean('status')->default(true);
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');

            $table->foreign('courier_id')
                ->references('id')
                ->on('couriers')
                ->onDelete('cascade');

            // IMPORTANT: Unique per company, per courier, per zone, per weight slab
            $table->unique(
                ['company_id', 'courier_id', 'zone_name', 'weight_slab_kg'],
                'courier_rate_unique'
            );

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courier_rate_card');
    }
};
