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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id'); 
            $table->unsignedBigInteger('company_id');           
            $table->enum('channel', ['email', 'sms', 'whatsapp','rcs']);
            $table->enum('user_type', ['buyer', 'seller', 'admin']);
            $table->string('event')->index();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->json('response')->nullable();
            $table->integer('sent_status')->index()->default(0);  
            $table->timestamps();
            $table->foreign('order_id')->references('id')->on('orders') 
            ->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies') 
            ->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers') 
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
