<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('delivery_orders')) {
            Schema::table('delivery_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('delivery_orders', 'carrier_name')) {
                    $table->string('carrier_name')->nullable()->after('status');
                }
                if (!Schema::hasColumn('delivery_orders', 'awb_number')) {
                    $table->string('awb_number')->nullable()->after('carrier_name');
                }
                if (!Schema::hasColumn('delivery_orders', 'shipping_method')) {
                    $table->string('shipping_method')->nullable()->after('awb_number');
                }
                if (!Schema::hasColumn('delivery_orders', 'notes')) {
                    $table->text('notes')->nullable()->after('shipping_method');
                }
                if (!Schema::hasColumn('delivery_orders', 'created_by')) {
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('notes');
                }
                if (!Schema::hasColumn('delivery_orders', 'dispatched_by')) {
                    $table->foreignId('dispatched_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
                }
            });
        }

        if (Schema::hasTable('delivery_order_items')) {
            Schema::table('delivery_order_items', function (Blueprint $table) {
                if (!Schema::hasColumn('delivery_order_items', 'order_item_id')) {
                    $table->foreignId('order_item_id')->nullable()->constrained('order_items')->cascadeOnDelete()->after('delivery_order_id');
                }
                if (!Schema::hasColumn('delivery_order_items', 'variant_id')) {
                    $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete()->after('product_id');
                }
                if (!Schema::hasColumn('delivery_order_items', 'unit_price')) {
                    $table->decimal('unit_price', 10, 2)->default(0.00)->after('qty_delivered');
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'fulfillment_status')) {
                    $table->string('fulfillment_status')->nullable()->default('unfulfilled')->after('status');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('delivery_orders')) {
            Schema::table('delivery_orders', function (Blueprint $table) {
                $table->dropColumn(['carrier_name', 'awb_number', 'shipping_method', 'notes', 'created_by', 'dispatched_by']);
            });
        }

        if (Schema::hasTable('delivery_order_items')) {
            Schema::table('delivery_order_items', function (Blueprint $table) {
                $table->dropColumn(['order_item_id', 'variant_id', 'unit_price']);
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'fulfillment_status')) {
                    $table->dropColumn('fulfillment_status');
                }
            });
        }
    }
};
