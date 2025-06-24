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
        Schema::create('lead_sources', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['person', 'company']);

            // Person fields (nullable if type is company)
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            // Company fields (nullable if type is person)
            $table->string('company_name')->nullable();
            $table->string('company_registration_number')->nullable();
            $table->string('company_vat_number')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_phone')->nullable();
            $table->text('company_address')->nullable();
            $table->string('manager_name')->nullable();
            $table->string('manager_email')->nullable();
            $table->string('manager_phone')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_sources');
    }
};
