<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Issue;
use App\Models\ProductRequest;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix outlet_id for existing issues based on product_request
        $issues = Issue::whereNotNull('product_request_id')->get();

        foreach ($issues as $issue) {
            $request = ProductRequest::find($issue->product_request_id);
            if ($request && $request->user_id) {
                // Update outlet_id and reset invoice_path to force regeneration
                $issue->update([
                    'outlet_id' => $request->user_id,
                    'invoice_path' => null 
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No distinct reverse operation possible without backup
    }
};
