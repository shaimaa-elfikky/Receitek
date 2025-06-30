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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            // Links the product to one of the tenant's categories
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            
            $table->string('code')->nullable()->after('name');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sku')->nullable()->comment('Stock Keeping Unit');
            $table->decimal('price', 10, 2)->default(0.0);
            $table->string('vat');
            $table->boolean('vat_included')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
