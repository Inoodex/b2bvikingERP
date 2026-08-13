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
        Schema::table('sales_quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_quotations', 'currency_id')) {
                $table->foreignId('currency_id')->nullable()->after('customer_id')->constrained('currencies')->onDelete('set null');
            }
            if (!Schema::hasColumn('sales_quotations', 'exchange_rate')) {
                $table->decimal('exchange_rate', 15, 6)->default(1.000000)->after('currency_id');
            }
            if (!Schema::hasColumn('sales_quotations', 'tax_id')) {
                $table->foreignId('tax_id')->nullable()->after('exchange_rate')->constrained('taxes')->onDelete('set null');
            }
            if (!Schema::hasColumn('sales_quotations', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
            if (!Schema::hasColumn('sales_quotations', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('notes')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('sales_quotations', 'reminder_sent')) {
                $table->boolean('reminder_sent')->default(false)->after('created_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_quotations', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropForeign(['tax_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['currency_id', 'exchange_rate', 'tax_id', 'notes', 'created_by', 'reminder_sent']);
        });
    }
};
