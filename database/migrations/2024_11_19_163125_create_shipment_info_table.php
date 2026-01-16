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
        Schema::create('shipment_info', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->unique();
            $table->unsignedBigInteger('company_id');
            $table->string('shipment_type');
            $table->unsignedBigInteger('courier_id');
            $table->string('tracking_id')->nullable();
            $table->decimal('applied_weight', 12, 2);
            $table->string('fulfillment_status')->default(false)->index();
            $table->string('current_status')->nullable()->index();
            $table->string('store_shipment_status')->nullable()->index();
            $table->timestamp('current_status_date')->nullable()->index();
            $table->string('origin')->nullable();
            $table->string('destination')->nullable();
            $table->timestamp('pickedup_date')->nullable();
            $table->unsignedBigInteger('pickedup_location_id');
            $table->text('pickedup_location_address')->nullable();
            $table->unsignedBigInteger('return_location_id');
            $table->text('return_location_address')->nullable();
            $table->timestamp('edd')->nullable();
            $table->string('pod')->nullable();         
            $table->boolean('manifest_created')->default(false)->index();
            $table->string('pickup_id')->nullable()->index();
            $table->string('payment_mode')->nullable();
            $table->boolean('label_generated')->default(false)->comment('0 = not generated, 1 = generated');
            $table->timestamps();
            $table->index(['company_id', 'label_generated'],'idx_company_label_generated');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('courier_id')->references('courier_id')->on('courier_settings');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('pickedup_location_id')->references('id')->on('pickup_locations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_info');
    }
};
