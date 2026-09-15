<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Traits\HasEphemeralPdfReports;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PayablesReceivablesReportController extends Controller
{
    use HasEphemeralPdfReports;

    /**
     * Check if a payables/receivables report PDF has completed generating in the background.
     */
    public function checkReportStatus(Request $request): JsonResponse
    {
        $reportType = (string) $request->get('type', '');
        $allowedTypes = ['ar_aging', 'customer_ledger', 'vendor_payments', 'ap_aging'];

        // Handle supplier_statement_{vendorId} dynamic prefix
        $isSupplierStatement = str_starts_with($reportType, 'supplier_statement_');

        if (!in_array($reportType, $allowedTypes, true) && !$isSupplierStatement) {
            return response()->json(['ready' => false]);
        }

        $after = (int) $request->get('after', 0);
        $latest = $this->getLatestReportFile($reportType);

        if (!$latest) {
            return response()->json(['ready' => false]);
        }

        if ($after > 0 && ($latest['timestamp'] ?? 0) < $after) {
            return response()->json(['ready' => false]);
        }

        return response()->json([
            'ready'        => true,
            'download_url' => $latest['url'],
            'filename'     => $latest['filename'],
            'time'         => $latest['time'],
            'date'         => $latest['date'],
            'timestamp'    => $latest['timestamp'],
        ]);
    }

    /**
     * Stream or download generated PDF from ephemeral storage.
     */
    public function downloadReportPdf(string $file): BinaryFileResponse|RedirectResponse
    {
        $cleanFile = basename($file);
        $path = storage_path('app/temp_reports/' . $cleanFile);

        if (!file_exists($path)) {
            Toastr::error('The requested report file has expired or was purged. Please generate a fresh report.');
            return redirect()->back();
        }

        return response()->download($path, $cleanFile, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
