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
        Schema::create('courier_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->unsignedBigInteger('channel_id')->index();
            $table->unsignedBigInteger('courier_id')->index();
            $table->string('courier_name', 191);
            $table->tinyInteger('status')->default(1)->comment('1 = active, 0 = inactive');
            $table->timestamps();       
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courier_mappings', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropIndex(['channel_id']);
            $table->dropIndex(['courier_id']);
        });
        Schema::dropIfExists('courier_mappings');
    }
};
