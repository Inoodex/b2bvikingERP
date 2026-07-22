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
        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('paid_amount', 15, 2)->default(0)->after('total_amount');
            $table->decimal('due_amount', 15, 2)->default(0)->after('paid_amount');
            $table->string('payment_status')->default('pending')->after('due_amount');
        });

        DB::table('purchases')->update([
            'paid_amount' => 0,
            'due_amount' => DB::raw('total_amount'),
            'payment_status' => 'pending',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['paid_amount', 'due_amount', 'payment_status']);
        });
    }
};
