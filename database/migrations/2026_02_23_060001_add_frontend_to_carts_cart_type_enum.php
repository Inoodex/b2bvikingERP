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
        // MySQL: modify the enum column to include 'frontend'
        DB::statement("ALTER TABLE carts MODIFY COLUMN cart_type ENUM('booking', 'request', 'frontend') NOT NULL DEFAULT 'booking'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE carts MODIFY COLUMN cart_type ENUM('booking', 'request') NOT NULL DEFAULT 'booking'");
    }
};
