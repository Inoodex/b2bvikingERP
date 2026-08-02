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
        Schema::table('vendor_return_items', function (Blueprint $table) {
            if (!Schema::hasColumn('vendor_return_items', 'unit_price')) {
                $table->decimal('unit_price', 15, 2)->default(0.00)->after('qty');
            }
            if (!Schema::hasColumn('vendor_return_items', 'total_amount')) {
                $table->decimal('total_amount', 15, 2)->default(0.00)->after('unit_price');
            }
            if (!Schema::hasColumn('vendor_return_items', 'reason')) {
                $table->string('reason')->nullable()->after('total_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_return_items', function (Blueprint $table) {
            $table->dropColumn(['unit_price', 'total_amount', 'reason']);
        });
    }
};
