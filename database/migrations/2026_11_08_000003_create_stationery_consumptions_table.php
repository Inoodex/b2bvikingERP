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
        Schema::create('stationery_consumptions', function (Blueprint $table) {
            $table->id();
            $table->string('issue_no', 60)->unique();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('department_name', 120)->nullable();
            $table->string('issued_to', 150)->nullable();
            $table->date('issue_date');
            $table->decimal('total_cost', 14, 2)->default(0.00);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('purpose_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('issue_date');
        });

        Schema::create('stationery_consumption_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stationery_consumption_id')->constrained('stationery_consumptions')->cascadeOnDelete();
            $table->foreignId('stationery_item_id')->constrained('stationery_items')->cascadeOnDelete();
            $table->decimal('quantity', 12, 2)->default(1.00);
            $table->decimal('unit_cost', 12, 2)->default(0.00); // Snapshot of WAC at issue time
            $table->decimal('subtotal', 14, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stationery_consumption_items');
        Schema::dropIfExists('stationery_consumptions');
    }
};

