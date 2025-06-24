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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketing_agent_id')->nullable()->constrained()->nullOnDelete();

            // Company Info
            $table->string('company_name');
            $table->string('company_registration_number');
            $table->string('company_vat_number')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_phone')->nullable();
            $table->text('company_address')->nullable();

            // Company Admin Credentials
            $table->string('manager_name');
            $table->string('manager_email')->unique(); 
            $table->string('password');
            $table->string('manager_phone')->nullable();
            $table->rememberToken();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
