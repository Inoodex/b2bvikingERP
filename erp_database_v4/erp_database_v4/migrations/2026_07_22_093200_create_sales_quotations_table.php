<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_no', 50)->unique();
            $table->foreignId('customer_id')->constrained('users');
            $table->foreignId('template_id')->nullable()->constrained('quotation_templates')->nullOnDelete();
            $table->string('incoterm', 20)->nullable();
            $table->date('valid_until')->nullable();
            $table->enum('status', ['draft','sent','accepted','rejected','expired'])->default('draft');
            $table->decimal('subtotal_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_quotations');
    }
};
