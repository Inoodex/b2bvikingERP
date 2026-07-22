<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function hasIndex(string $table, string $indexName): bool
    {
        $database = DB::getDatabaseName();

        $row = DB::selectOne(
            'SELECT COUNT(*) AS total FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $table, $indexName]
        );

        return ((int) ($row->total ?? 0)) > 0;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if ($this->hasIndex('carts', 'carts_user_id_product_id_cart_type_unique')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->dropUnique('carts_user_id_product_id_cart_type_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->hasIndex('carts', 'carts_user_id_product_id_cart_type_unique')) {
            return;
        }

        $hasDuplicate = DB::table('carts')
            ->select('user_id', 'product_id', 'cart_type', DB::raw('COUNT(*) as total'))
            ->groupBy('user_id', 'product_id', 'cart_type')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if (!$hasDuplicate) {
            Schema::table('carts', function (Blueprint $table) {
                $table->unique(['user_id', 'product_id', 'cart_type'], 'carts_user_id_product_id_cart_type_unique');
            });
        }
    }
};

