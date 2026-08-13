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
        Schema::table('sales_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_invoices', 'subtotal_amount')) {
                $table->decimal('subtotal_amount', 15, 2)->default(0.00)->after('invoice_no');
            }
            if (!Schema::hasColumn('sales_invoices', 'tax_amount')) {
                $table->decimal('tax_amount', 15, 2)->default(0.00)->after('subtotal_amount');
            }
            if (!Schema::hasColumn('sales_invoices', 'discount_amount')) {
                $table->decimal('discount_amount', 15, 2)->default(0.00)->after('tax_amount');
            }
            if (!Schema::hasColumn('sales_invoices', 'due_date')) {
                $table->date('due_date')->nullable()->after('date');
            }
            if (!Schema::hasColumn('sales_invoices', 'paid_amount')) {
                $table->decimal('paid_amount', 15, 2)->default(0.00)->after('total_amount');
            }
            if (!Schema::hasColumn('sales_invoices', 'due_amount')) {
                $table->decimal('due_amount', 15, 2)->default(0.00)->after('paid_amount');
            }
            if (!Schema::hasColumn('sales_invoices', 'currency_id')) {
                $table->foreignId('currency_id')->nullable()->after('due_amount')->constrained('currencies')->onDelete('set null');
            }
            if (!Schema::hasColumn('sales_invoices', 'exchange_rate')) {
                $table->decimal('exchange_rate', 15, 6)->default(1.000000)->after('currency_id');
            }
            if (!Schema::hasColumn('sales_invoices', 'incoterm')) {
                $table->string('incoterm')->nullable()->after('exchange_rate');
            }
            if (!Schema::hasColumn('sales_invoices', 'notes')) {
                $table->text('notes')->nullable()->after('incoterm');
            }
            if (!Schema::hasColumn('sales_invoices', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('notes')->constrained('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'subtotal_amount', 'tax_amount', 'discount_amount', 'due_date',
                'paid_amount', 'due_amount', 'currency_id', 'exchange_rate',
                'incoterm', 'notes', 'created_by'
            ]);
        });
    }
};
