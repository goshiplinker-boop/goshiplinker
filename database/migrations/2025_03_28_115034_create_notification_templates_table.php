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
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->enum('channel', ['email', 'sms', 'whatsapp','rcs']);
            $table->enum('user_type', ['admin', 'seller', 'buyer']);
            $table->string('event_type'); // e.g., "new_seller", "order_placed"
            $table->text('body'); // Message template
            $table->json('meta')->nullable(); // Extra details (subject, sender_id, template_id)
            $table->boolean('status')->default(1); // 1 = active, 0 = inactive
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
