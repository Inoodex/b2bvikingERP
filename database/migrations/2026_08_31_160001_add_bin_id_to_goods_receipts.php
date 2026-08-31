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
        if (Schema::hasTable('goods_receipts') && !Schema::hasColumn('goods_receipts', 'bin_id')) {
            Schema::table('goods_receipts', function (Blueprint $table) {
                $table->foreignId('bin_id')->nullable()->after('outlet_id')->constrained('warehouse_bins')->nullOnDelete();
            });
        }

        if (Schema::hasTable('goods_receipt_items') && !Schema::hasColumn('goods_receipt_items', 'bin_id')) {
            Schema::table('goods_receipt_items', function (Blueprint $table) {
                $table->foreignId('bin_id')->nullable()->after('variant_id')->constrained('warehouse_bins')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('goods_receipts') && Schema::hasColumn('goods_receipts', 'bin_id')) {
            Schema::table('goods_receipts', function (Blueprint $table) {
                $table->dropForeign(['bin_id']);
                $table->dropColumn('bin_id');
            });
        }

        if (Schema::hasTable('goods_receipt_items') && Schema::hasColumn('goods_receipt_items', 'bin_id')) {
            Schema::table('goods_receipt_items', function (Blueprint $table) {
                $table->dropForeign(['bin_id']);
                $table->dropColumn('bin_id');
            });
        }
    }
};
