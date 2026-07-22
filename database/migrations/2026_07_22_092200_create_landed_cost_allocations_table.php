<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landed_cost_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_detail_id')->constrained('purchase_details')->cascadeOnDelete();
            $table->foreignId('lc_expense_id')->nullable()->constrained('lc_expenses')->nullOnDelete();
            $table->decimal('allocated_amount', 15, 2);
            $table->decimal('landed_unit_cost', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landed_cost_allocations');
    }
};
