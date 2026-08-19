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
        // 1. Upgrade stock_adjustments table
        Schema::table('stock_adjustments', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_adjustments', 'adjustment_type')) {
                $table->enum('adjustment_type', ['increase', 'decrease', 'reconciliation'])->default('decrease')->after('outlet_id');
            }
            if (!Schema::hasColumn('stock_adjustments', 'reason_code')) {
                $table->enum('reason_code', [
                    'damage',
                    'physical_count',
                    'expired',
                    'sample_marketing',
                    'theft_loss',
                    'internal_use',
                    'other'
                ])->default('physical_count')->after('adjustment_type');
            }
            if (!Schema::hasColumn('stock_adjustments', 'total_items_count')) {
                $table->integer('total_items_count')->default(0)->after('status');
            }
            if (!Schema::hasColumn('stock_adjustments', 'total_adjusted_cost')) {
                $table->decimal('total_adjusted_cost', 15, 2)->default(0.00)->after('total_items_count');
            }
            if (!Schema::hasColumn('stock_adjustments', 'note')) {
                $table->text('note')->nullable()->after('total_adjusted_cost');
            }
            if (!Schema::hasColumn('stock_adjustments', 'attachment')) {
                $table->string('attachment')->nullable()->after('note');
            }
        });

        // 2. Upgrade stock_adjustment_items table
        Schema::table('stock_adjustment_items', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_adjustment_items', 'system_qty')) {
                $table->decimal('system_qty', 12, 2)->default(0.00)->after('variant_id');
            }
            if (!Schema::hasColumn('stock_adjustment_items', 'counted_qty')) {
                $table->decimal('counted_qty', 12, 2)->default(0.00)->after('system_qty');
            }
            if (!Schema::hasColumn('stock_adjustment_items', 'adjusted_qty')) {
                $table->decimal('adjusted_qty', 12, 2)->default(0.00)->after('counted_qty');
            }
            if (!Schema::hasColumn('stock_adjustment_items', 'unit_cost')) {
                $table->decimal('unit_cost', 15, 2)->default(0.00)->after('adjusted_qty');
            }
            if (!Schema::hasColumn('stock_adjustment_items', 'total_cost')) {
                $table->decimal('total_cost', 15, 2)->default(0.00)->after('unit_cost');
            }
            if (!Schema::hasColumn('stock_adjustment_items', 'item_note')) {
                $table->string('item_note')->nullable()->after('total_cost');
            }
        });

        // 3. Upgrade stock_transfers table
        Schema::table('stock_transfers', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_transfers', 'dispatched_by')) {
                $table->unsignedBigInteger('dispatched_by')->nullable()->after('requested_by');
            }
            if (!Schema::hasColumn('stock_transfers', 'received_by')) {
                $table->unsignedBigInteger('received_by')->nullable()->after('dispatched_by');
            }
            if (!Schema::hasColumn('stock_transfers', 'transfer_date')) {
                $table->date('transfer_date')->nullable()->after('status');
            }
            if (!Schema::hasColumn('stock_transfers', 'dispatched_at')) {
                $table->timestamp('dispatched_at')->nullable()->after('transfer_date');
            }
            if (!Schema::hasColumn('stock_transfers', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('dispatched_at');
            }
            if (!Schema::hasColumn('stock_transfers', 'challan_no')) {
                $table->string('challan_no', 100)->nullable()->after('received_at');
            }
            if (!Schema::hasColumn('stock_transfers', 'vehicle_no')) {
                $table->string('vehicle_no', 100)->nullable()->after('challan_no');
            }
            if (!Schema::hasColumn('stock_transfers', 'driver_name')) {
                $table->string('driver_name', 150)->nullable()->after('vehicle_no');
            }
            if (!Schema::hasColumn('stock_transfers', 'driver_phone')) {
                $table->string('driver_phone', 50)->nullable()->after('driver_name');
            }
            if (!Schema::hasColumn('stock_transfers', 'total_items_count')) {
                $table->integer('total_items_count')->default(0)->after('note');
            }
        });

        // 4. Upgrade stock_transfer_items table
        Schema::table('stock_transfer_items', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_transfer_items', 'received_qty')) {
                $table->decimal('received_qty', 12, 2)->nullable()->after('qty');
            }
            if (!Schema::hasColumn('stock_transfer_items', 'unit_cost')) {
                $table->decimal('unit_cost', 15, 2)->default(0.00)->after('received_qty');
            }
            if (!Schema::hasColumn('stock_transfer_items', 'item_note')) {
                $table->string('item_note')->nullable()->after('unit_cost');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe reversible migration
    }
};
