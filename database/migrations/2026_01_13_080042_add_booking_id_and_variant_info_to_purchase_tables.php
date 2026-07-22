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
        Schema::table('purchases', function (Blueprint $table) {
            $table->unsignedBigInteger('booking_id')->nullable()->after('vendor_id');
        });

        Schema::table('purchase_details', function (Blueprint $table) {
            $table->json('variant_info')->nullable()->after('qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('booking_id');
        });

        Schema::table('purchase_details', function (Blueprint $table) {
            $table->dropColumn('variant_info');
        });
    }
};
