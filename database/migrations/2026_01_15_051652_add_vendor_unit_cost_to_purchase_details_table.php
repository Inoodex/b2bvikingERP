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
        Schema::table('purchase_details', function (Blueprint $table) {
            $table->decimal('unit_cost_vendor', 20, 2)->after('qty')->default(0);
            $table->decimal('raw_material_cost', 20, 2)->after('unit_cost_vendor')->default(0);
            $table->decimal('tax_cost', 20, 2)->after('raw_material_cost')->default(0);
            $table->decimal('transport_cost', 20, 2)->after('tax_cost')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_details', function (Blueprint $table) {
            $table->dropColumn(['unit_cost_vendor', 'raw_material_cost', 'tax_cost', 'transport_cost']);
        });
    }
};
