<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_batches', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_batches', 'goods_receipt_id')) {
                $table->foreignId('goods_receipt_id')->nullable()->after('outlet_id')->constrained('goods_receipts')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_batches', function (Blueprint $table) {
            $table->dropForeign(['goods_receipt_id']);
            $table->dropColumn(['goods_receipt_id']);
        });
    }
};
