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
        if (Schema::hasTable('purchase_details') && !Schema::hasColumn('purchase_details', 'landed_cost')) {
            Schema::table('purchase_details', function (Blueprint $table) {
                $table->decimal('landed_cost', 15, 2)->nullable()->after('unit_cost');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('purchase_details') && Schema::hasColumn('purchase_details', 'landed_cost')) {
            Schema::table('purchase_details', function (Blueprint $table) {
                $table->dropColumn('landed_cost');
            });
        }
    }
};
