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
        Schema::create('pickup_locations', function (Blueprint $table) {
            $table->id(); // auto-increment primary key
            $table->string('location_title');
            $table->string('contact_person_name');
            $table->string('email');
            $table->unsignedBigInteger('phone')->nullable();
            $table->unsignedBigInteger('alternate_phone')->nullable();
            $table->text('address');
            $table->string('landmark')->nullable();
            $table->unsignedInteger('zipcode');
            $table->string('city');
            $table->string('state_code', 2);
            $table->string('country_code', 3);
            $table->enum('location_type', ['home', 'office', 'other']);
            $table->tinyInteger('pickup_day')->nullable();
            $table->time('pickup_time')->nullable();
            $table->string('brand_name');
            $table->unsignedInteger('company_id')->index();
            $table->string('gstin')->nullable();
            $table->unsignedBigInteger('courier_warehouse_id')->nullable();
            $table->boolean('default')->default(1)->index();
            $table->boolean('status')->default(1)->index();
            $table->timestamps();            
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pickup_locations');
    }
};
