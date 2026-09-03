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
        $tables = [
            'stock_transfers',
            'vendor_returns',
            'letters_of_credit',
            'vendor_bills',
            'fund_transfers',
            'product_requests',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'approval_status')) {
                        $table->string('approval_status', 30)->default('approved')->after('id');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'stock_transfers',
            'vendor_returns',
            'letters_of_credit',
            'vendor_bills',
            'fund_transfers',
            'product_requests',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'approval_status')) {
                        $table->dropColumn('approval_status');
                    }
                });
            }
        }
    }
};
