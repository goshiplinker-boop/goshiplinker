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
        Schema::create('couriers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // name (varchar)
            $table->unsignedBigInteger('parent_id')->nullable()->index(); // parent_id (int, nullable)
            $table->unsignedBigInteger('company_id')->index(); // company_id (int)
            $table->string('image_url')->nullable(); // image (varchar, nullable)
            $table->boolean('status')->default(true); // status (boolean, default true)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('couriers');
    }
};
