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
        if (!Schema::hasTable('credit_notes')) {
            Schema::create('credit_notes', function (Blueprint $table) {
                $table->id();
                $table->string('credit_note_no')->unique();
                $table->foreignId('sales_return_id')->nullable()->constrained('sales_returns')->onDelete('cascade');
                $table->foreignId('customer_id')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('currency_id')->nullable()->constrained('currencies')->onDelete('set null');
                $table->decimal('amount', 15, 2)->default(0.00);
                $table->decimal('settled_amount', 15, 2)->default(0.00);
                $table->decimal('remaining_amount', 15, 2)->default(0.00);
                $table->enum('settlement_status', ['unsettled', 'partial', 'settled'])->default('unsettled');
                $table->enum('settlement_mode', ['invoice_deduction', 'product_replacement', 'direct_refund'])->default('invoice_deduction');
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_notes');
    }
};
