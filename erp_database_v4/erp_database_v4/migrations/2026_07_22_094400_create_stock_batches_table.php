<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->foreignId('outlet_id')->nullable()->constrained('outlets')->nullOnDelete();
            $table->foreignId('purchase_detail_id')->nullable()->constrained('purchase_details')->nullOnDelete();
            $table->string('batch_no', 100)->nullable();
            $table->decimal('qty_received', 10, 2);
            $table->decimal('qty_remaining', 10, 2);
            $table->decimal('unit_cost', 15, 2);
            $table->date('received_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_batches');
    }
};
