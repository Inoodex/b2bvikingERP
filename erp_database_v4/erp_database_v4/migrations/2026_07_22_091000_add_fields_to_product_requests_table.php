<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * NOTE: `status` was `enum('pending','approved','rejected','shipped','completed')`.
 * We add 'cancelled' via raw SQL (Blueprint::change() would need doctrine/dbal
 * installed just for this one column, so a raw ALTER is simpler and safer here).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_requests', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('user_id')->constrained('departments')->nullOnDelete();
            $table->foreignId('outlet_id')->nullable()->after('department_id')->constrained('outlets')->nullOnDelete();
            $table->enum('request_type', ['store_requisition', 'purchase_requisition'])->default('purchase_requisition')->after('outlet_id');
        });

        DB::statement("ALTER TABLE product_requests MODIFY status ENUM('pending','approved','rejected','cancelled','shipped','completed') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE product_requests MODIFY status ENUM('pending','approved','rejected','shipped','completed') NOT NULL DEFAULT 'pending'");

        Schema::table('product_requests', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['outlet_id']);
            $table->dropColumn(['department_id', 'outlet_id', 'request_type']);
        });
    }
};
