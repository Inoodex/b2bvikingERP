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
        Schema::create('stationery_stock_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stationery_item_id')->constrained('stationery_items')->cascadeOnDelete();
            $table->string('transaction_type', 40); // purchase_in, consumption_out, adjustment_in, adjustment_out
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_no', 80)->nullable();
            $table->date('date');
            $table->decimal('stock_before', 12, 2)->default(0.00);
            $table->decimal('qty_in', 12, 2)->default(0.00);
            $table->decimal('qty_out', 12, 2)->default(0.00);
            $table->decimal('stock_after', 12, 2)->default(0.00);
            $table->decimal('unit_cost', 12, 2)->default(0.00);
            $table->decimal('total_value', 14, 2)->default(0.00);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['stationery_item_id', 'date']);
            $table->index('transaction_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stationery_stock_ledgers');
    }
};

