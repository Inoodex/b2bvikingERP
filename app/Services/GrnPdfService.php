<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Support\PdfCacheManager;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class GrnPdfService
{
    /**
     * Get or generate PDF content for a Goods Receipt Note.
     * Checks Storage public disk first. If missing or expired, renders synchronously and caches it.
     */
    public function getOrGeneratePdf(GoodsReceipt $grn): string
    {
        $grn->load(['purchase.vendor', 'outlet', 'receivedBy', 'items.product', 'items.variant']);

        $path = "grns/grn_{$grn->id}.pdf";

        if (PdfCacheManager::isFresh($path, 3600)) {
            return Storage::disk('public')->get($path);
        }

        $pdfContent = $this->generatePdfContent($grn);
        Storage::disk('public')->put($path, $pdfContent);

        return $pdfContent;
    }

    /**
     * Regenerate PDF and overwrite cache.
     */
    public function regeneratePdf(GoodsReceipt $grn): string
    {
        $grn->load(['purchase.vendor', 'outlet', 'receivedBy', 'items.product', 'items.variant']);
        $path = "grns/grn_{$grn->id}.pdf";

        $pdfContent = $this->generatePdfContent($grn);
        Storage::disk('public')->put($path, $pdfContent);

        return $pdfContent;
    }

    /**
     * Helper to render dompdf view.
     */
    private function generatePdfContent(GoodsReceipt $grn): string
    {
        $pdf = Pdf::loadView('backend.grn.pdf', compact('grn'))
            ->setPaper('a4', 'portrait')
            ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);

        return $pdf->output();
    }
}
