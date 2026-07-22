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
        Schema::table('orders', function (Blueprint $table) {
            $table->json('pi_info')->nullable()->after('placed_at');
        });

        Schema::table('product_requests', function (Blueprint $table) {
            $table->json('pi_info')->nullable()->after('admin_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_requests', function (Blueprint $table) {
            $table->dropColumn('pi_info');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('pi_info');
        });
    }
};
