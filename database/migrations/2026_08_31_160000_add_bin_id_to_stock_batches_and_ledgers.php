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
        if (Schema::hasTable('stock_batches') && !Schema::hasColumn('stock_batches', 'bin_id')) {
            Schema::table('stock_batches', function (Blueprint $table) {
                $table->unsignedBigInteger('bin_id')->nullable()->after('outlet_id');
            });
        }

        if (Schema::hasTable('stock_ledgers') && !Schema::hasColumn('stock_ledgers', 'bin_id')) {
            Schema::table('stock_ledgers', function (Blueprint $table) {
                $table->unsignedBigInteger('bin_id')->nullable()->after('outlet_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('stock_batches') && Schema::hasColumn('stock_batches', 'bin_id')) {
            Schema::table('stock_batches', function (Blueprint $table) {
                $table->dropColumn('bin_id');
            });
        }

        if (Schema::hasTable('stock_ledgers') && Schema::hasColumn('stock_ledgers', 'bin_id')) {
            Schema::table('stock_ledgers', function (Blueprint $table) {
                $table->dropColumn('bin_id');
            });
        }
    }
};
