<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_counts', function (Blueprint $table) {
            $table->id();
            $table->string('session_name');
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('outlets')->nullOnDelete(); 
            $table->string('status')->default('draft'); // draft, in_progress, completed, validated
            $table->foreignId('counted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock_count_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_count_id')->constrained('stock_counts')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->decimal('system_qty', 10, 2)->default(0);
            $table->decimal('physical_qty', 10, 2)->default(0);
            $table->decimal('variance', 10, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_count_lines');
        Schema::dropIfExists('stock_counts');
    }
};
