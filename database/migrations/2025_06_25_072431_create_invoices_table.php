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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');

            $table->string('invoice_number')->unique();
            $table->date('issue_date');
            $table->date('due_date');
            $table->boolean('tax_included')->default(false);

            $table->decimal('subtotal', 10, 2);
            $table->decimal('total_discount', 10, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('total', 10, 2);

            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
