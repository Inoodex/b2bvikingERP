<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letters_of_credit', function (Blueprint $table) {
            $table->id();
            $table->string('lc_no', 50)->unique();
            $table->foreignId('proforma_invoice_id')->nullable()->constrained('proforma_invoices')->nullOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->string('issuing_bank')->nullable();
            $table->decimal('margin_percent', 5, 2)->nullable();
            $table->decimal('amount', 15, 2);
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['open','amended','closed','cancelled'])->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letters_of_credit');
    }
};
