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
        // 1. Packaging Types (Box, Carton, Pallet, Container, etc.)
        if (!Schema::hasTable('packaging_types')) {
            Schema::create('packaging_types', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('code', 20)->unique();
                $table->unsignedSmallInteger('level_order')->default(1);
                $table->decimal('tare_weight', 8, 3)->nullable()->comment('Tare weight in kg');
                $table->decimal('max_weight', 8, 3)->nullable()->comment('Max capacity weight in kg');
                $table->decimal('length_cm', 8, 2)->nullable();
                $table->decimal('width_cm', 8, 2)->nullable();
                $table->decimal('height_cm', 8, 2)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 2. Product Packagings (N-tier packaging rules per product/variant)
        if (!Schema::hasTable('product_packagings')) {
            Schema::create('product_packagings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
                $table->foreignId('packaging_type_id')->constrained('packaging_types')->cascadeOnDelete();
                $table->foreignId('parent_packaging_type_id')->nullable()->constrained('packaging_types')->nullOnDelete();
                $table->unsignedInteger('units_per_pack')->default(1);
                $table->unsignedInteger('packs_per_parent')->nullable();
                $table->string('barcode', 100)->nullable()->index();
                $table->timestamps();
            });
        }

        // 3. Handling Units (Physical cartons, boxes, pallets, containers)
        if (!Schema::hasTable('handling_units')) {
            Schema::create('handling_units', function (Blueprint $table) {
                $table->id();
                $table->string('hu_code', 100)->unique()->index();
                $table->foreignId('packaging_type_id')->constrained('packaging_types')->cascadeOnDelete();
                $table->foreignId('parent_handling_unit_id')->nullable()->constrained('handling_units')->nullOnDelete();
                $table->unsignedBigInteger('warehouse_id')->nullable()->index();
                $table->string('status', 50)->default('packed')->index(); // draft, packed, sealed, shipped, received, unpacked
                $table->unsignedInteger('total_quantity')->default(0);
                $table->decimal('gross_weight', 8, 3)->nullable();
                $table->string('batch_no', 100)->nullable()->index();
                $table->text('notes')->nullable();
                $table->foreignId('packed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('packed_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 4. Handling Unit Items (Contents inside each physical Handling Unit)
        if (!Schema::hasTable('handling_unit_items')) {
            Schema::create('handling_unit_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('handling_unit_id')->constrained('handling_units')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
                $table->string('batch_no', 100)->nullable();
                $table->unsignedInteger('quantity')->default(1);
                $table->decimal('unit_price', 12, 2)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('handling_unit_items');
        Schema::dropIfExists('handling_units');
        Schema::dropIfExists('product_packagings');
        Schema::dropIfExists('packaging_types');
    }
};
