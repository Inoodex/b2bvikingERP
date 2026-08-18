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
        if (!Schema::hasTable('customer_payments')) {
            Schema::create('customer_payments', function (Blueprint $table) {
                $table->id();
                $table->string('payment_no')->unique(); // e.g. REC-202608-0001
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Customer
                $table->foreignId('sales_invoice_id')->nullable()->constrained('sales_invoices')->onDelete('set null');
                $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
                $table->foreignId('account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
                $table->decimal('amount', 15, 2);
                $table->string('payment_method')->default('cash'); // cash, bank_transfer, cheque, card, mobile_money
                $table->string('reference_no')->nullable(); // Cheque #, Transaction ID, Bank Ref
                $table->date('payment_date');
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->string('status')->default('posted'); // posted, voided
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_payments');
    }
};
