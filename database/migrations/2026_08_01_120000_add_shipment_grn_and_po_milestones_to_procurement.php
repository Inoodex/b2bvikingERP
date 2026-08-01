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
        // 1. Add purchase_type enum to product_requests
        if (Schema::hasTable('product_requests') && !Schema::hasColumn('product_requests', 'purchase_type')) {
            Schema::table('product_requests', function (Blueprint $table) {
                $table->enum('purchase_type', ['local', 'foreign'])->default('local')->after('request_type');
            });
        }

        // 2. Add bl_awb_no and document_path to shipments
        if (Schema::hasTable('shipments')) {
            Schema::table('shipments', function (Blueprint $table) {
                if (!Schema::hasColumn('shipments', 'bl_awb_no')) {
                    $table->string('bl_awb_no', 100)->nullable()->after('container_no');
                }
                if (!Schema::hasColumn('shipments', 'document_path')) {
                    $table->string('document_path')->nullable()->after('status');
                }
            });
        }

        // 3. Add remarks to goods_receipts
        if (Schema::hasTable('goods_receipts') && !Schema::hasColumn('goods_receipts', 'remarks')) {
            Schema::table('goods_receipts', function (Blueprint $table) {
                $table->text('remarks')->nullable()->after('qc_status');
            });
        }

        // 4. Expand milestone_status ENUM in purchases table to include Step 3 states ('shipped', 'goods_partial', 'goods_received')
        if (Schema::hasTable('purchases') && Schema::hasColumn('purchases', 'milestone_status')) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE purchases MODIFY milestone_status ENUM('draft', 'pending_approval', 'approved', 'po_sent', 'pi_attached', 'lc_opened', 'shipped', 'goods_partial', 'goods_received', 'cancelled') NOT NULL DEFAULT 'draft'");
        }

        // 5. Expand status ENUM in shipments table to include 'cancelled'
        if (Schema::hasTable('shipments') && Schema::hasColumn('shipments', 'status')) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE shipments MODIFY status ENUM('in_transit', 'arrived', 'cleared', 'cancelled') NOT NULL DEFAULT 'in_transit'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('product_requests') && Schema::hasColumn('product_requests', 'purchase_type')) {
            Schema::table('product_requests', function (Blueprint $table) {
                $table->dropColumn('purchase_type');
            });
        }

        if (Schema::hasTable('shipments')) {
            Schema::table('shipments', function (Blueprint $table) {
                if (Schema::hasColumn('shipments', 'bl_awb_no')) {
                    $table->dropColumn('bl_awb_no');
                }
                if (Schema::hasColumn('shipments', 'document_path')) {
                    $table->dropColumn('document_path');
                }
            });
        }

        if (Schema::hasTable('goods_receipts') && Schema::hasColumn('goods_receipts', 'remarks')) {
            Schema::table('goods_receipts', function (Blueprint $table) {
                $table->dropColumn('remarks');
            });
        }
    }
};
