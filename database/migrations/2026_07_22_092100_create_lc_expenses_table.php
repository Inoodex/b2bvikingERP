<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lc_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lc_id')->constrained('letters_of_credit')->cascadeOnDelete();
            $table->string('cost_element', 50);  // CD, RD, SD, VAT, AIT, AT, LC Margin, Opening Charge, Doc Handling, Insurance, Transport, Freight, C&F
            $table->decimal('amount', 15, 2);
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->boolean('goes_to_unit_cost')->default(true);  // Unit Cost Configuration (2.22)
            $table->unsignedBigInteger('gl_account_id')->nullable();  // FK wired in Accounting module step
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lc_expenses');
    }
};
