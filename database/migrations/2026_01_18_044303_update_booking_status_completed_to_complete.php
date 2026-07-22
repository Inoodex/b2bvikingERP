<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update any existing 'completed' status to 'complete'
        DB::table('bookings')
            ->where('status', 'completed')
            ->update(['status' => 'complete']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert 'complete' back to 'completed'
        DB::table('bookings')
            ->where('status', 'complete')
            ->update(['status' => 'completed']);
    }
};
