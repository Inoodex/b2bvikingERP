<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('grn_no', 50)->unique();
            $table->foreignId('purchase_id')->constrained('purchases');
            $table->foreignId('outlet_id')->constrained('outlets');
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('qc_status', ['pending','passed','partial','failed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_receipts');
    }
};
