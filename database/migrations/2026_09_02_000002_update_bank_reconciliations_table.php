<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bank_reconciliations', function (Blueprint $table) {
            if (!Schema::hasColumn('bank_reconciliations', 'gl_balance')) {
                $table->decimal('gl_balance', 15, 2)->default(0.00)->after('statement_balance');
            }
            if (!Schema::hasColumn('bank_reconciliations', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('reconciled_by')->constrained('users')->nullOnDelete();
            }
        });

        // Modify status column to support 'reconciled', 'discrepancy', 'in_progress', 'completed'
        try {
            DB::statement("ALTER TABLE `bank_reconciliations` MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'in_progress'");
        } catch (\Throwable $e) {
            // In case DB is SQLite or already varchar
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_reconciliations', function (Blueprint $table) {
            if (Schema::hasColumn('bank_reconciliations', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('bank_reconciliations', 'gl_balance')) {
                $table->dropColumn('gl_balance');
            }
        });
    }
};
