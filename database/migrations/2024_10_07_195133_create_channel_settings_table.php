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
        Schema::create('channel_settings', function (Blueprint $table) {
            $table->id();
            $table->string('channel_title'); // Channel title
            $table->string('channel_url')->nullable()->unique(); // Channel URL
            $table->string('client_id')->nullable(); // Client ID
            $table->string('secret_key')->nullable()->unique(); // Secret key
            $table->string('brand_logo')->nullable(); // Logo (nullable)
            $table->string('brand_name'); // Brand name
            $table->unsignedBigInteger('company_id')->index(); // Company ID (indexed)
            $table->unsignedBigInteger('channel_id')->index(); // Channel ID (indexed)
            $table->string('channel_code'); // Channel code
            $table->boolean('webhooks_create')->nullable();
            $table->text('other_details')->nullable(); // Channel details (nullable)
            $table->boolean('status')->default(1)->index(); // Status (default 1, indexed)
            $table->timestamps();
            $table->foreign('channel_id')->references('id')->on('channels')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_settings');
    }
};
