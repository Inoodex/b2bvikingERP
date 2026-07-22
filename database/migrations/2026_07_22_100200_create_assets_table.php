<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('asset_code', 50)->unique();
            $table->string('name');
            $table->string('category', 100)->nullable();
            $table->decimal('purchase_value', 15, 2);
            $table->date('purchase_date');
            $table->enum('depreciation_method', ['straight_line','reducing_balance'])->default('straight_line');
            $table->unsignedTinyInteger('useful_life_years')->nullable();
            $table->foreignId('outlet_id')->nullable()->constrained('outlets')->nullOnDelete();
            $table->enum('status', ['active','disposed'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
