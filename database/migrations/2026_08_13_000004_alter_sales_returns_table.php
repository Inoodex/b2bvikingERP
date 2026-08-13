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
        Schema::table('sales_returns', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_returns', 'sales_invoice_id')) {
                $table->foreignId('sales_invoice_id')->nullable()->after('order_id')->constrained('sales_invoices')->onDelete('set null');
            }
            if (!Schema::hasColumn('sales_returns', 'return_to_stock')) {
                $table->boolean('return_to_stock')->default(true)->after('refund_method');
            }
            if (!Schema::hasColumn('sales_returns', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('approved_by')->constrained('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_returns', function (Blueprint $table) {
            $table->dropForeign(['sales_invoice_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['sales_invoice_id', 'return_to_stock', 'created_by']);
        });
    }
};
