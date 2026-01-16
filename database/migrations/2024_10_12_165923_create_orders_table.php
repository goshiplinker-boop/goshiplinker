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
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); // Primary key

            // Vendor & Channel Info
            $table->string('vendor_order_id')->nullable()->index();
            $table->string('vendor_order_number')->nullable()->index();
            $table->unsignedBigInteger('channel_id')->index();
            $table->dateTime('channel_order_date')->nullable();
            $table->string('status_code')->nullable();

            // Company & Customer Info
            $table->unsignedBigInteger('customer_id')->index();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('fullname');
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();

            // Shipping Details
            $table->string('s_fullname')->nullable();
            $table->string('s_company')->nullable();
            $table->text('s_complete_address')->nullable();
            $table->string('s_landmark')->nullable();
            $table->string('s_phone')->nullable();
            $table->string('s_zipcode', 10)->nullable();
            $table->string('s_city')->nullable();
            $table->string('s_state_code', 10)->nullable();
            $table->string('s_country_code', 3)->nullable();

            // Billing Details
            $table->string('b_fullname')->nullable();
            $table->string('b_company')->nullable();
            $table->text('b_complete_address')->nullable();
            $table->string('b_landmark')->nullable();
            $table->string('b_phone')->nullable();
            $table->string('b_zipcode', 10)->nullable();
            $table->string('b_city')->nullable();
            $table->string('b_state_code', 10)->nullable();
            $table->string('b_country_code', 3)->nullable();

            // Payment & Financial Info
            $table->string('financial_status')->nullable();
            $table->string('payment_mode')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('currency_code', 3)->nullable();

            // Invoice Info
            $table->string('invoice_prefix')->nullable();
            $table->string('invoice_number')->nullable();

            // Package Info
            $table->enum('package_type', ['SPS', 'MPS'])->default('SPS');
            $table->float('package_length')->default(10);
            $table->float('package_breadth')->default(10);
            $table->float('package_height')->default(10);
            $table->float('package_dead_weight')->default(0.05);

            // Other Details
            $table->text('notes')->nullable();
            $table->string('order_tags')->nullable();
            $table->decimal('sub_total', 10, 2)->default(0.00);
            $table->decimal('order_total', 10, 2)->default(0.00);
            $table->string('customer_ip_address', 45)->nullable(); // IPv4 & IPv6
            $table->integer('rate_card_id')->nullable();
            $table->timestamps(); 
            $table->softDeletes(); // deleted_at
            $table->index(['company_id', 'channel_order_date'], 'idx_company_channel_order_date');
            $table->index(['id', 'rate_card_id'], 'idx_id_rate_card_id');
            // Foreign Keys
            $table->foreign('channel_id')
                ->references('id')
                ->on('channels')
                ->onDelete('cascade');

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->unique(['vendor_order_number', 'channel_id', 'company_id'],'unique_vendor_channel_company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
