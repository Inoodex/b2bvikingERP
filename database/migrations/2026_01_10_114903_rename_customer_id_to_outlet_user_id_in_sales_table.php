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
        Schema::table('sales', function (Blueprint $table) {
            $table->renameColumn('customer_id', 'outlet_user_id');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreign('outlet_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['outlet_user_id']);
            $table->renameColumn('outlet_user_id', 'customer_id');
        });
    }
};
