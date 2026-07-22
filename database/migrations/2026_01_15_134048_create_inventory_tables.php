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
        // 1. Inventory Stock (Current stock per outlet/variant)
        Schema::create('inventory_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->onDelete('cascade');
            $table->foreignId('outlet_id')->nullable(); // For multi-outlet support
            $table->decimal('quantity', 10, 2)->default(0);
            $table->timestamps();
        });

        // 2. Stock Ledger (History of movements)
        Schema::create('stock_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->onDelete('cascade');
            $table->foreignId('outlet_id')->nullable();
            $table->string('reference_type'); // e.g., 'purchase', 'issue', 'adjustment'
            $table->unsignedBigInteger('reference_id');
            $table->decimal('in_qty', 10, 2)->default(0);
            $table->decimal('out_qty', 10, 2)->default(0);
            $table->decimal('balance_qty', 10, 2)->default(0);
            $table->date('date');
            $table->timestamps();
        });

        // 3. Issues (Stock issuance to outlets/internal)
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->string('issue_no')->unique();
            $table->foreignId('outlet_id')->nullable(); 
            $table->enum('status', ['pending', 'confirmed'])->default('pending');
            $table->decimal('total_qty', 10, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('issue_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->onDelete('cascade');
            $table->decimal('quantity', 10, 2);
            $table->timestamps();
        });

        // 4. Booking Items (Master-Detail for Booking)
        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->onDelete('cascade');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->decimal('total_price', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_items');
        Schema::dropIfExists('issue_items');
        Schema::dropIfExists('issues');
        Schema::dropIfExists('stock_ledgers');
        Schema::dropIfExists('inventory_stocks');
    }
};
