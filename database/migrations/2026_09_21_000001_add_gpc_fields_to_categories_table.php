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
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'gpc_code')) {
                $table->string('gpc_code', 20)->nullable()->index()->after('slug');
            }
            if (!Schema::hasColumn('categories', 'gpc_title')) {
                $table->string('gpc_title', 255)->nullable()->after('gpc_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'gpc_title')) {
                $table->dropColumn('gpc_title');
            }
            if (Schema::hasColumn('categories', 'gpc_code')) {
                $table->dropColumn('gpc_code');
            }
        });
    }
};
