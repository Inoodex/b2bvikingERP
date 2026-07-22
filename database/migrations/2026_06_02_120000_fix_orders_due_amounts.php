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
        // For orders created before account fields were added, ensure due_amount reflects total
        DB::table('orders')
            ->where('total_amount', '>', 0)
            ->where('paid_amount', 0)
            ->where('due_amount', 0)
            ->update([
                'due_amount' => DB::raw('total_amount'),
                'payment_status' => 'pending',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert only rows that look like they were changed by this migration
        DB::table('orders')
            ->where('total_amount', '>', 0)
            ->where('paid_amount', 0)
            ->whereRaw('due_amount = total_amount')
            ->update([
                'due_amount' => 0,
                'payment_status' => 'pending',
            ]);
    }
};
