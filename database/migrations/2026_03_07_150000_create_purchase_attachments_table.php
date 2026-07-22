<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        $legacyAttachments = DB::table('purchases')
            ->whereNotNull('invoice_attachment')
            ->where('invoice_attachment', '!=', '')
            ->get(['id', 'invoice_attachment', 'user_id', 'created_at', 'updated_at']);

        if ($legacyAttachments->isNotEmpty()) {
            $now = now();
            $rows = [];

            foreach ($legacyAttachments as $legacy) {
                $rows[] = [
                    'purchase_id' => $legacy->id,
                    'file_path' => $legacy->invoice_attachment,
                    'original_name' => basename($legacy->invoice_attachment),
                    'mime_type' => null,
                    'file_size' => null,
                    'uploaded_by' => $legacy->user_id,
                    'created_at' => $legacy->created_at ?? $now,
                    'updated_at' => $legacy->updated_at ?? $now,
                ];
            }

            DB::table('purchase_attachments')->insert($rows);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_attachments');
    }
};
