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
        if (Schema::hasColumn('payment_settings', 'max_limit')) {
            Schema::table('payment_settings', function (Blueprint $table) {
                $table->dropColumn('max_limit');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('payment_settings', 'max_limit')) {
            Schema::table('payment_settings', function (Blueprint $table) {
                $table->decimal('max_limit', 12, 2)->nullable()->after('client_secret');
            });
        }
    }
};
