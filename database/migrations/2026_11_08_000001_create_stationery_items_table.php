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
        Schema::create('stationery_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code', 50)->unique();
            $table->string('name', 255);
            $table->string('category', 60)->default('packaging'); // packaging, office_supplies, printing_labels, shipping_materials, warehouse_tools, other
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('unit_name', 50)->default('Pcs');
            $table->decimal('current_stock', 12, 2)->default(0.00);
            $table->decimal('unit_cost', 12, 2)->default(0.00); // Weighted Average Unit Cost (WAC)
            $table->decimal('min_alert_qty', 12, 2)->default(10.00);
            $table->boolean('allow_sale')->default(false); // Future-ready toggle for commercial sales
            $table->boolean('status')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('category');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stationery_items');
    }
};

