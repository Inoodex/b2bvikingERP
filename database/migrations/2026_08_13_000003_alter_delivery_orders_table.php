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
        Schema::table('delivery_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_orders', 'delivery_type')) {
                $table->enum('delivery_type', ['full', 'partial'])->default('full')->after('delivery_no');
            }
            if (!Schema::hasColumn('delivery_orders', 'tracking_number')) {
                $table->string('tracking_number')->nullable()->after('delivery_type');
            }
            if (!Schema::hasColumn('delivery_orders', 'carrier_name')) {
                $table->string('carrier_name')->nullable()->after('tracking_number');
            }
            if (!Schema::hasColumn('delivery_orders', 'shipping_address')) {
                $table->text('shipping_address')->nullable()->after('carrier_name');
            }
            if (!Schema::hasColumn('delivery_orders', 'actual_delivery_date')) {
                $table->date('actual_delivery_date')->nullable()->after('date');
            }
            if (!Schema::hasColumn('delivery_orders', 'notes')) {
                $table->text('notes')->nullable()->after('actual_delivery_date');
            }
            if (!Schema::hasColumn('delivery_orders', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('notes')->constrained('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'delivery_type', 'tracking_number', 'carrier_name',
                'shipping_address', 'actual_delivery_date', 'notes', 'created_by'
            ]);
        });
    }
};
