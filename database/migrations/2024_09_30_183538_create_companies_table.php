]<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id(); // Auto-incrementing ID
            $table->foreignId('parent_id')->nullable()->index();
            $table->string('legal_registered_name'); // Company registered name
            $table->string('pan_number')->nullable(); 
            $table->string('pan_image')->nullable(); 
            $table->string('company_email_id')->unique(); // Email ID (unique)
            $table->unsignedBigInteger('phone_number')->nullable();
            $table->string('brand_name')->nullable(); // brand name (optional)
            $table->string('brand_logo')->nullable(); // Logo (optional)
            $table->string('website_url')->nullable(); // Website URL (optional)
            $table->text('address')->nullable(); // Address (optional)
            $table->string('pincode')->nullable(); // Pincode
            $table->string('city')->nullable(); // City
            $table->string('state_code', 2)->nullable(); // State code (limited to 2 chars)
            $table->string('country_code', 3)->nullable(); // Country code (limited to 3 chars)
            $table->string('shipment_weight')->nullable();
            $table->string('channel_name')->nullable();
            $table->string('courier_using')->nullable();
            $table->string('product_category')->nullable();
            $table->string('monthly_orders')->nullable(); // Number of monthly orders
            $table->integer('lead_status_id')->default(1)->index();
            $table->string('subscription_plan')->nullable();//plans like Trial ,Free,Basic etc
            $table->boolean('subscription_status')->nullable()->index();//1 active/ o expired
            $table->integer('company_type_id')->nullable()->index();
            $table->string('doc_type')->nullable()->index();
            $table->string('doc_number')->nullable();
            $table->text('doc_urls')->nullable();
            $table->text('bank_details')->nullable();   
            $table->boolean('kyc_verification')->default(0)->index();
            $table->text('utm_data')->nullable();            
            $table->boolean('status')->default(1)->index(); // Status (active/inactive)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('companies');
    }
}