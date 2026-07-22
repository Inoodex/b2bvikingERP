<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lc_expenses', function (Blueprint $table) {
            $table->foreign('gl_account_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lc_expenses', function (Blueprint $table) {
            $table->dropForeign(['gl_account_id']);
        });
    }
};
