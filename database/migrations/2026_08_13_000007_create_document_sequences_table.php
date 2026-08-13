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
        if (!Schema::hasTable('document_sequences')) {
            Schema::create('document_sequences', function (Blueprint $table) {
                $table->id();
                $table->string('model_type')->unique(); // e.g. SalesQuotation, SalesOrder, SalesInvoice, DeliveryOrder, CreditNote
                $table->string('prefix')->default('');
                $table->string('suffix')->nullable();
                $table->integer('padding')->default(4);
                $table->integer('next_number')->default(1);
                $table->enum('reset_policy', ['yearly', 'monthly', 'never'])->default('yearly');
                $table->boolean('include_date')->default(true);
                $table->string('date_format')->default('Ym');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_sequences');
    }
};
