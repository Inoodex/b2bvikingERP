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
        Schema::create('customer_product_visibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            
            // Target customer identifier (One or more can be specified)
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('phone_number')->nullable()->index();

            // Override rule
            $table->enum('visibility_mode', ['force_in_stock', 'force_out_of_stock', 'hide_product'])->default('force_in_stock');
            $table->integer('reserved_qty')->nullable()->default(null);
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Indexes for lightning fast lookups
            $table->index(['product_id', 'user_id']);
            $table->index(['product_id', 'company_id']);
            $table->index(['product_id', 'outlet_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_product_visibilities');
    }
};
