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
        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_status_id')->constrained('lead_statuses')->onDelete('cascade'); // Assuming it links to lead_status table
            $table->text('last_remarks')->nullable(); // This will store the last remark
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // User performing the activity
            $table->foreignId('company_id')->constrained()->onDelete('cascade'); // User performing the activity
            $table->timestamp('followup_date')->nullable();           
            $table->boolean('is_followup_completed')->nullable(); // Indicates if the activity is done           
            $table->text('remarks')->nullable(); // Additional remarks
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_activities');
    }
};
