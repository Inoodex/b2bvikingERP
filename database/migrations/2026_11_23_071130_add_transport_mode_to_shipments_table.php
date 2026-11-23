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
        Schema::table('shipments', function (Blueprint $table) {
            $table->enum('transport_mode', ['sea', 'air', 'road', 'rail'])
                ->default('sea')
                ->after('purchase_id')
                ->index();
            $table->string('carrier_name', 255)
                ->nullable()
                ->after('transport_mode');
            $table->text('mode_change_reason')
                ->nullable()
                ->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['transport_mode', 'carrier_name', 'mode_change_reason']);
        });
    }
};
