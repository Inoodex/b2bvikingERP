<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\SalesInvoice;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('sales_invoices', 'payment_token')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                $table->string('payment_token', 64)->nullable()->unique()->after('invoice_no');
            });
        }

        // Backfill existing invoices with unique tokens
        foreach (SalesInvoice::whereNull('payment_token')->get() as $inv) {
            $inv->payment_token = Str::random(40);
            $inv->saveQuietly();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('sales_invoices', 'payment_token')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                $table->dropColumn('payment_token');
            });
        }
    }
};
