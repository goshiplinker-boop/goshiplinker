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
        Schema::create('seller_wallet_ledger', function (Blueprint $table) {
            $table->id();
            // Wallet owner
            $table->unsignedBigInteger('company_id')
                  ->comment('FK to companies.id');

            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('shipment_id')->nullable();

            $table->unsignedBigInteger('courier_id')->nullable();
            $table->string('courier_code', 50)->nullable();
            $table->string('tracking_number', 100)->nullable();

            $table->enum('transaction_type', [
                'freight_charge',
                'freight_reversal',
                'cod_reversal',
                'additional_weight_charge',
                'additional_weight_reversal',
                'wallet_topup',
                'adjustment',
            ]);

            $table->enum('direction', ['debit', 'credit']);
            $table->decimal('cod_charges', 12, 2);
            $table->decimal('amount', 12, 2);
            $table->decimal('opening_balance', 12, 2);
            $table->decimal('closing_balance', 12, 2);

            $table->string('description', 255)->nullable();

            $table->enum('source', [
                'system',
                'manual',
                'api',
                'cron',
            ])->default('system');

            $table->timestamps();

            /* Indexes */
            $table->index('company_id');
            $table->index('shipment_id');
            $table->index('order_id');
            $table->index('tracking_number');

            /* Prevent double freight charge */
            $table->unique(
                ['shipment_id', 'tracking_number', 'transaction_type'],
                'uniq_shipment_tracking_type'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_wallet_ledger');
    }
};
