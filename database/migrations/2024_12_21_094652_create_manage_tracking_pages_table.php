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
        Schema::create('manage_tracking_page', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id'); // Foreign key to companies table
            $table->string('website_domain'); // Website domain
            $table->text('custom_style_script')->nullable(); // Custom styles and scripts (optional)
            $table->json('json_data')->nullable(); // JSON data (optional)
            $table->boolean('status')->default(1); // Status (1 for active, 0 for inactive)
            $table->timestamps(); // Created at and updated at timestamps
            // Foreign key constraint
        $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {   
        
        Schema::dropIfExists('manage_tracking_page');
    }
};
