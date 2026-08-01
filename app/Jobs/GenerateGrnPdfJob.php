<?php

namespace App\Jobs;

use App\Models\GoodsReceipt;
use App\Services\GrnPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateGrnPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $grnId;

    public function __construct(int $grnId)
    {
        $this->grnId = $grnId;
    }

    public function handle(GrnPdfService $pdfService): void
    {
        $grn = GoodsReceipt::find($this->grnId);
        if (!$grn) {
            Log::error("GenerateGrnPdfJob: GRN #{$this->grnId} not found.");
            return;
        }

        try {
            $pdfService->regeneratePdf($grn);
            Log::info("GenerateGrnPdfJob: PDF generated and cached for GRN #{$grn->grn_no}");
        } catch (\Throwable $e) {
            Log::error("GenerateGrnPdfJob Exception for GRN #{$grn->grn_no}: " . $e->getMessage());
            throw $e;
        }
    }
}
