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
            $table->unsignedInteger('minimum_order_qty')
                ->default(1)
                ->after('price');

            $table->decimal('discount', 8, 2)
                ->default(0)
                ->after('minimum_order_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $dropColumns = [];

            if (Schema::hasColumn('products', 'minimum_order_qty')) {
                $dropColumns[] = 'minimum_order_qty';
            }

            if (Schema::hasColumn('products', 'discount')) {
                $dropColumns[] = 'discount';
            }

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });    
    }
};
