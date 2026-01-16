<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImportTrackingNumbersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('import_tracking_numbers', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('courier_id'); // Foreign key for courier
            $table->unsignedBigInteger('company_id'); // Foreign key for company
            $table->string('tracking_number'); // Tracking number
            $table->string('payment_type')->nullable();
            $table->boolean('used')->default(0); // 0 or 1
            $table->timestamps(); // Created at and Updated at

            // Foreign key constraints with cascading
            $table->foreign('courier_id')->references('id')->on('couriers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('import_tracking_numbers');
    }
}
