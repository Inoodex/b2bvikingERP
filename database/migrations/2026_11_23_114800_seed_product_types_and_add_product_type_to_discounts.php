<?php

use App\Models\ProductType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Seed standard occasion types in product_types table
        $occasions = [
            'Winter Item',
            'Summer Item',
            'Christmas Promo',
            'Spring Collection',
            'Black Friday Special',
            'New Year Special',
        ];

        foreach ($occasions as $name) {
            ProductType::firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'status' => 1,
                ]
            );
        }

        // 2. Add product_type_id to discounts table
        if (Schema::hasTable('discounts') && !Schema::hasColumn('discounts', 'product_type_id')) {
            Schema::table('discounts', function (Blueprint $table) {
                $table->unsignedBigInteger('product_type_id')->nullable()->after('is_seasonal');
                $table->foreign('product_type_id')->references('id')->on('product_types')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('discounts') && Schema::hasColumn('discounts', 'product_type_id')) {
            Schema::table('discounts', function (Blueprint $table) {
                $table->dropForeign(['product_type_id']);
                $table->dropColumn('product_type_id');
            });
        }
    }
};

