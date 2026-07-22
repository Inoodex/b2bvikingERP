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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_no')->unique();
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            
            // Product details snapshot or override?
            // "Product Number", "Product Name" etc are already in Product. I will just reference Product.
            // But I will store the *transaction* values.
            
            $table->integer('qty')->default(0);
            $table->double('unit_price')->default(0); // Purchase price per unit at this booking
            $table->double('extra_cost')->default(0);
            $table->double('total_cost')->default(0); // (unit_price * qty) + extra_cost
            
            $table->double('sale_price')->nullable(); // Intended sale price
            
            $table->text('description')->nullable();
            
            // Min fields
            $table->integer('min_inventory_qty')->nullable();
            $table->integer('min_sale_qty')->nullable();
            $table->double('min_purchase_price')->nullable();
            
            $table->json('variant_info')->nullable(); // e.g. {"color": "Red", "size": "L"}
            $table->string('barcode')->nullable();
            
            $table->json('custom_fields')->nullable();
            
            $table->string('status')->default("pending");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
