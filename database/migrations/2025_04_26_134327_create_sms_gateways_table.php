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
        Schema::create('sms_gateways', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id'); 
            $table->string('gateway_name');
            $table->enum('http_method', ['GET', 'POST'])->default('GET');
            $table->string('gateway_url');
            $table->string('dlt_header_name')->nullable();
            $table->string('dlt_header_id')->nullable();
            $table->string('dlt_template_name')->nullable();
            $table->string('dlt_template_id')->nullable();
            $table->string('mobile');
            $table->text('other_parameters')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_gateways');
    }
};
