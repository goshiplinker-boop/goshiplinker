<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuyerInvoiceSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buyer_invoice_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id'); // Foreign key to associate with company
            $table->string('number_type'); // 'order_number' or 'custom_number'
            $table->string('prefix')->nullable(); // Make 'prefix' column nullable
            $table->integer('start_from')->nullable(); // Make 'start_from' column nullable
            $table->string('invoice_type'); // 'thermal_4x6' or 'classic_a4'
            $table->timestamps();
            // Optional: Foreign key constraint if company table exists
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('buyer_invoice_settings');
    }
}
