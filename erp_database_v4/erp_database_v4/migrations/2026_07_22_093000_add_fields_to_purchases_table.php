<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->enum('purchase_type', ['local','foreign'])->default('local')->after('vendor_id');
            $table->foreignId('rfq_id')->nullable()->after('purchase_type')->constrained('rfqs')->nullOnDelete();
            $table->foreignId('comparison_statement_id')->nullable()->after('rfq_id')->constrained('comparison_statements')->nullOnDelete();
            $table->foreignId('proforma_invoice_id')->nullable()->after('comparison_statement_id')->constrained('proforma_invoices')->nullOnDelete();
            $table->foreignId('lc_id')->nullable()->after('proforma_invoice_id')->constrained('letters_of_credit')->nullOnDelete();
            $table->foreignId('currency_id')->nullable()->after('lc_id')->constrained('currencies')->nullOnDelete();
            $table->decimal('foreign_amount', 15, 2)->nullable()->after('currency_id');
            $table->decimal('exchange_rate_used', 15, 6)->nullable()->after('foreign_amount');
            $table->decimal('base_amount', 15, 2)->nullable()->after('exchange_rate_used');
            $table->enum('approval_status', ['pending','level1_approved','approved','rejected'])->default('approved')->after('base_amount');  // default 'approved' so existing historical POs aren't retroactively blocked; separate from existing `status` (active/void flag)
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['rfq_id']);
            $table->dropForeign(['comparison_statement_id']);
            $table->dropForeign(['proforma_invoice_id']);
            $table->dropForeign(['lc_id']);
            $table->dropForeign(['currency_id']);
            $table->dropColumn(['rfq_id', 'comparison_statement_id', 'proforma_invoice_id', 'lc_id', 'currency_id', 'purchase_type', 'foreign_amount', 'exchange_rate_used', 'base_amount', 'approval_status']);
        });
    }
};
