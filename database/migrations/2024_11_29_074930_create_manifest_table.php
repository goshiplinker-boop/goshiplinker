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
        Schema::create('manifests', function (Blueprint $table) {
            $table->id();            
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('courier_id');
            $table->unsignedBigInteger('pickup_location_id');
            $table->string('payment_mode'); 
            $table->boolean('pickup_created')->default(0)->index();             
            $table->timestamps();
            $table->foreign('courier_id')->references('courier_id')->on('courier_settings');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('pickup_location_id')->references('id')->on('pickup_locations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manifests');
    }
};
