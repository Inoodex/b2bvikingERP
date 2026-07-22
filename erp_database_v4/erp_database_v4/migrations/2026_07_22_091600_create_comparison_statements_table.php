<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comparison_statements', function (Blueprint $table) {
            $table->id();
            $table->string('cs_no', 50)->unique();
            $table->foreignId('rfq_id')->constrained('rfqs')->cascadeOnDelete();
            $table->foreignId('recommended_vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->enum('status', ['draft','pending','approved','rejected'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comparison_statements');
    }
};
