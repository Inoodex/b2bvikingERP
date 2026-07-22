<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->enum('payment_type', ['order_payment','purchase_payment','advance_payment']);
            $table->unsignedBigInteger('payment_id');
            $table->enum('invoice_type', ['order','purchase']);
            $table->unsignedBigInteger('invoice_id');
            $table->decimal('matched_amount', 15, 2);
            $table->timestamp('allocated_at')->nullable();
            $table->timestamps();
            $table->index(['payment_type', 'payment_id']);
            $table->index(['invoice_type', 'invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
    }
};
