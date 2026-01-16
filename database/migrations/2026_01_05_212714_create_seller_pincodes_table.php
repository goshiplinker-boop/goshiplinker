<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_pincodes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('courier_id');

            // Fixed-length pincode (India)
            $table->char('pincode', 6);

            // Active / Inactive flag
            $table->boolean('status')
                  ->default(1)
                  ->comment('1 = Active, 0 = Inactive');

            $table->timestamps();

            /**
             * ✅ BUSINESS RULE
             * One pincode per company + courier
             */
            $table->unique(
                ['company_id', 'courier_id', 'pincode'],
                'uniq_company_courier_pincode'
            );
            
            /**
             * Foreign keys
             */
            $table->foreign('company_id')
                  ->references('id')
                  ->on('companies')
                  ->onDelete('cascade');

            // courier_id → couriers.parent_id
            $table->foreign('courier_id')
                  ->references('parent_id')
                  ->on('couriers')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_pincodes');
    }
};
