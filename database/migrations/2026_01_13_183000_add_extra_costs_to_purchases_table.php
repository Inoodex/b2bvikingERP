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
        Schema::table('purchases', function (Blueprint $table) {
            $table->double('material_cost')->default(0)->after('total_amount');
            $table->double('transport_cost')->default(0)->after('material_cost');
            $table->double('tax')->default(0)->after('transport_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['material_cost', 'transport_cost', 'tax']);
        });
    }
};
