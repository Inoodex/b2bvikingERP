<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `stock_adjustments` MODIFY COLUMN `status` ENUM('draft', 'pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'draft'");
        DB::statement("ALTER TABLE `stock_transfers` MODIFY COLUMN `status` ENUM('draft', 'pending', 'pending_approval', 'dispatched', 'received', 'completed', 'cancelled') NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe reversible
    }
};
