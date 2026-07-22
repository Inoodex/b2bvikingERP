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
        Schema::table('product_requests', function (Blueprint $table) {
            $table->double('total_amount')->default(0)->after('total_qty');
        });

        Schema::table('product_request_items', function (Blueprint $table) {
            $table->double('unit_price')->default(0)->after('qty');
            $table->double('subtotal')->default(0)->after('unit_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_requests', function (Blueprint $table) {
            $table->dropColumn('total_amount');
        });

        Schema::table('product_request_items', function (Blueprint $table) {
            $table->dropColumn(['unit_price', 'subtotal']);
        });
    }
};
