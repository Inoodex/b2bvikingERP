<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('carts', 'variant_id')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->foreignId('variant_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained('product_variants')
                    ->nullOnDelete();
            });
        }

        // Drop old unique index (user_id, product_id, cart_type) so variants can exist separately.
        try {
            DB::statement('ALTER TABLE carts DROP INDEX carts_user_id_product_id_cart_type_unique');
        } catch (\Throwable $e) {
            // Ignore if index does not exist in this environment.
        }

        // Add variant-aware unique index.
        try {
            Schema::table('carts', function (Blueprint $table) {
                $table->unique(['user_id', 'product_id', 'cart_type', 'variant_id'], 'carts_user_product_type_variant_unique');
            });
        } catch (\Throwable $e) {
            // Ignore if already exists.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('carts', function (Blueprint $table) {
                $table->dropUnique('carts_user_product_type_variant_unique');
            });
        } catch (\Throwable $e) {
            // Ignore if index does not exist.
        }

        try {
            Schema::table('carts', function (Blueprint $table) {
                $table->unique(['user_id', 'product_id', 'cart_type']);
            });
        } catch (\Throwable $e) {
            // Ignore if already exists.
        }

        if (Schema::hasColumn('carts', 'variant_id')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->dropForeign(['variant_id']);
                $table->dropColumn('variant_id');
            });
        }
    }
};

