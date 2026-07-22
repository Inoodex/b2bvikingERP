<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lc_amendments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lc_id')->constrained('letters_of_credit')->cascadeOnDelete();
            $table->string('amendment_no', 50);
            $table->text('change_details');
            $table->date('amended_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lc_amendments');
    }
};
