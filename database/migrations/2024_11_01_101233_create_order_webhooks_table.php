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
        Schema::create('order_webhooks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index(); // Foreign key for company
            $table->unsignedBigInteger('order_id')->nullable()->index(); // Foreign key for order
            $table->string('channel_id')->index(); // Channel ID (as a string)
            $table->string('channel_order_id'); // Channel order ID (nullable)
            $table->string('webhook_type'); // Type of webhook (e.g., 'ORDERS_CREATE')
            $table->string('status')->default('0')->index();
            $table->text('webhook_data'); // JSON or serialized data from the webhook
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_webhooks');
    }
};
