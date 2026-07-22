<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advance_payments', function (Blueprint $table) {
            $table->id();
            $table->enum('party_type', ['vendor','customer']);
            $table->unsignedBigInteger('party_id');
            $table->decimal('amount', 15, 2);
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->decimal('applied_amount', 15, 2)->default(0);
            $table->decimal('balance', 15, 2);
            $table->date('payment_date');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['party_type', 'party_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advance_payments');
    }
};
