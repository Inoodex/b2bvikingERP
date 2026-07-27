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
        Schema::table('comparison_statements', function (Blueprint $table) {
            $table->renameColumn('status', 'approval_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comparison_statements', function (Blueprint $table) {
            $table->renameColumn('approval_status', 'status');
        });
    }
};
