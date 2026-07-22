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
            if (!Schema::hasColumn('products', 'discount_type')) {
                $table->string('discount_type', 20)->nullable()->after('discount');
            }

            if (!Schema::hasColumn('products', 'vat_type')) {
                $table->string('vat_type', 20)->nullable()->after('tax');
            }

            if (!Schema::hasColumn('products', 'vat_value')) {
                $table->decimal('vat_value', 10, 2)->nullable()->after('vat_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $dropColumns = [];

            if (Schema::hasColumn('products', 'vat_value')) {
                $dropColumns[] = 'vat_value';
            }

            if (Schema::hasColumn('products', 'vat_type')) {
                $dropColumns[] = 'vat_type';
            }

            if (Schema::hasColumn('products', 'discount_type')) {
                $dropColumns[] = 'discount_type';
            }

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
