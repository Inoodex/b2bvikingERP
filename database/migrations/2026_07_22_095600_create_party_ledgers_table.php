<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('party_ledgers', function (Blueprint $table) {
            $table->id();
            $table->enum('party_type', ['vendor','customer']);  // vendor -> Supplier Ledger, customer -> Customer Statement
            $table->unsignedBigInteger('party_id');  // vendors.id or users.id depending on party_type
            $table->string('reference_type', 100);
            $table->unsignedBigInteger('reference_id');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->date('entry_date');
            $table->timestamps();
            $table->index(['party_type', 'party_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('party_ledgers');
    }
};
