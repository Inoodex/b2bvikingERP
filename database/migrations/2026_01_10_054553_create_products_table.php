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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('thumb_image')->nullable();
            
            // Foreign Keys
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('brand_id')->nullable()->constrained('brands')->onDelete('set null');
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null');

            $table->string('product_number')->nullable(); // User requested "product number"
            $table->string('sku')->nullable();

            $table->integer('qty')->default(0);
            $table->text('short_description')->nullable();
            $table->text('long_description')->nullable();

            $table->double('purchase_price')->default(0);
            $table->double('price')->default(0); // Sale Price
            
            $table->string('barcode')->nullable();
            
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
