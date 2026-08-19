<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\ProductRequest;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('issues')) {
            $issues = DB::table('issues')->whereNotNull('product_request_id')->get();

            foreach ($issues as $issue) {
                $request = ProductRequest::find($issue->product_request_id);
                if ($request && $request->user_id) {
                    DB::table('issues')->where('id', $issue->id)->update([
                        'outlet_id' => $request->user_id,
                        'invoice_path' => null 
                    ]);
                }
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
