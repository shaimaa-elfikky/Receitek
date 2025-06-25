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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            // Links the service to the tenant who owns it
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            // Links the service to one of the tenant's categories
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');

            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0.0);
            // A field to describe the pricing model, e.g., "Per Hour", "One-time"
            $table->string('duration')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
