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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->nullable(); // Track who created it
            $table->date('date');
            $table->double('total_amount')->default(0);
            $table->text('note')->nullable();
            $table->boolean('status')->default(1); // 1 = Active/Completed? or Draft? Let's say 1=Completed for now (simple)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
