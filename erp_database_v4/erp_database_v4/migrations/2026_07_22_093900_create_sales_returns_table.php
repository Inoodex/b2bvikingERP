<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_no', 50)->unique();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('credit_note_no', 50)->nullable();
            $table->decimal('refund_amount', 15, 2)->default(0);
            $table->enum('refund_method', ['refund_to_source','bank','store_credit'])->nullable();
            $table->enum('status', ['pending','approved','rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_returns');
    }
};
