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
        Schema::table('products', function (Blueprint $table) {
            $table->string('self_number')->nullable()->after('barcode');
            $table->double('raw_material_cost')->default(0)->after('self_number');
            $table->double('transport_cost')->default(0)->after('raw_material_cost');
            $table->double('tax')->default(0)->after('transport_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['self_number', 'raw_material_cost', 'transport_cost', 'tax']);
        });
    }
};
