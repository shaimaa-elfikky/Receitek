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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('client_type'); // 'B2B' or 'B2C'

            // Common / B2C Fields
            $table->string('name_en'); // Mandatory: Client Name for B2C, Company Name for B2B
            $table->string('name_ar');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable(); // This is the single address field for B2C

            // B2B Specific Fields
            $table->string('cr_number')->nullable();
            $table->string('vat_number')->nullable();

            // --- NEW: B2B Detailed Address Fields ---
            $table->string('building_no')->nullable();
            $table->string('street_name')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('additional_no')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
