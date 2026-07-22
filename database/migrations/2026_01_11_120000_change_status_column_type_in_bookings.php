<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Change the column to string
        DB::statement("ALTER TABLE bookings MODIFY status VARCHAR(20) DEFAULT 'pending'");

        // 2. Normalize existing data (assuming 1=complete, 0=pending/cancelled? let's stick to user request)
        // Previous default was 1 (Active?). Let's map 1->complete, 0->pending.
        DB::table('bookings')->where('status', '1')->update(['status' => 'complete']);
        DB::table('bookings')->where('status', '0')->update(['status' => 'pending']);
        // Any others?
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to boolean/tinyint
        // First map strings back to 0/1 to avoid errors if strict?
        DB::table('bookings')->where('status', 'complete')->update(['status' => '1']);
        DB::table('bookings')->where('status', 'pending')->update(['status' => '0']);
        DB::table('bookings')->where('status', 'cancelled')->update(['status' => '0']); // Lossy

        DB::statement("ALTER TABLE bookings MODIFY status TINYINT(1) DEFAULT 1");
    }
};
