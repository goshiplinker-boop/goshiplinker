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
        Schema::create('courier_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('courier_id')->index();
            $table->string('courier_code'); // Varchar for carrier_code
            $table->string('courier_title'); // Varchar for carrier_title
            $table->enum('env_type', ['dev', 'live'])->default('dev');
            $table->text('courier_details')->nullable(); // Optional text for carrier_details
            $table->boolean('status')->default(1)->index(); // Boolean status (1 for active, 0 for inactive)
            $table->timestamps();
            $table->foreign('courier_id')->references('id')->on('couriers')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courier_settings');
    }
};
