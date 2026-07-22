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
        Schema::create('issue_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_no')->unique();
            $table->foreignId('issue_id')->constrained('issues')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->foreignId('outlet_id')->constrained('users')->onDelete('cascade');
            $table->decimal('refund_amount', 15, 2)->default(0.00);
            $table->text('note')->nullable();
            $table->string('status')->default('pending'); // pending, approved, cancelled
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('issue_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_return_id')->constrained('issue_returns')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->onDelete('set null');
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->string('condition')->default('good'); // good, damaged
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issue_return_items');
        Schema::dropIfExists('issue_returns');
    }
};
