<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ItemWisePurchaseDataTable;
use App\DataTables\PoStatusDataTable;
use App\DataTables\PrStatusDataTable;
use App\DataTables\SupplierWisePurchaseDataTable;
use App\Http\Controllers\Controller;
use App\Jobs\GeneratePurchaseVsLastYearPdfJob;
use App\Jobs\GenerateTotalPurchaseValuePdfJob;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Vendor;
use App\Services\PurchaseReportService;
use App\Traits\HasEphemeralPdfReports;
use Flasher\Toastr\Prime\ToastrFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PurchaseReportController extends Controller
{
    use HasEphemeralPdfReports;

    protected PurchaseReportService $reportService;

    public function __construct(PurchaseReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Supplier-wise Purchase Report
     */
    public function supplierWise(SupplierWisePurchaseDataTable $dataTable, Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'vendor_id']);
        $vendors = Vendor::where('status', 1)->get();

        return $dataTable->render('backend.purchase_report.supplier_wise', compact('vendors', 'filters'));
    }

    /**
     * Item-wise Purchase Report
     */
    public function itemWise(ItemWisePurchaseDataTable $dataTable, Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'vendor_id', 'product_id']);
        $vendors = Vendor::where('status', 1)->get();
        $products = Product::where('status', 1)->get();

        return $dataTable->render('backend.purchase_report.item_wise', compact('vendors', 'products', 'filters'));
    }

    /**
     * Total Purchase Value Periodic
     */
    public function totalValue(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'vendor_id']);
        $result = $this->reportService->getTotalPurchaseValue($filters);
        $summary = $result['summary'];
        $reportData = $result['reportData'];
        $vendors = Vendor::where('status', 1)->orderBy('shop_name')->get();
        $latestPdf = $this->getLatestReportFile('total_purchase_value_report', 'admin.purchase-reports.total-value.pdf.download');

        return view('backend.purchase_report.total_value', compact('summary', 'reportData', 'filters', 'vendors', 'latestPdf'));
    }

    /**
     * Async PDF Generation Dispatch for Total Purchase Value
     */
    public function totalValuePdfAsync(Request $request): JsonResponse
    {
        $filters = $request->only(['start_date', 'end_date', 'vendor_id']);
        GenerateTotalPurchaseValuePdfJob::dispatch($filters, auth()->id() ?? 0);

        return response()->json([
            'status'        => 'success',
            'message'       => 'Total Purchase Value PDF generation started in background.',
            'dispatched_at' => time(),
        ]);
    }

    /**
     * Check if Total Purchase Value or Vs Last Year PDF is ready
     */
    public function checkReportStatus(Request $request): JsonResponse
    {
        $reportType = (string) $request->get('type', 'total_purchase_value_report');
        $after = (int) $request->get('after', 0);

        $downloadRoute = match ($reportType) {
            'purchase_vs_last_year_report' => 'admin.purchase-reports.vs-last-year.pdf.download',
            default                        => 'admin.purchase-reports.total-value.pdf.download',
        };

        $latest = $this->getLatestReportFile($reportType, $downloadRoute);
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
     * Download generated PDF from ephemeral storage
     */
    public function downloadReportPdf(string $file): BinaryFileResponse|RedirectResponse
    {
        $cleanFile = basename($file);
        $path = storage_path('app/temp_reports/' . $cleanFile);

        if (!file_exists($path)) {
            toastr()->error('The requested report file has expired or was purged. Please generate a fresh report.');
            return redirect()->back();
        }

        return response()->download($path, $cleanFile, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Purchase Value vs Last Year Comparison
     */
    public function vsLastYear(Request $request)
    {
        $year = (int) $request->query('year', now()->year);
        $benchmarkYear = $request->filled('benchmark_year') ? (int) $request->query('benchmark_year') : null;
        $comparison = $this->reportService->getPurchaseVsLastYear($year, $benchmarkYear);
        $benchmarkYear = $comparison['benchmark_year'];

        $currentYear = (int) now()->year;
        $rangeYears = range($currentYear + 2, $currentYear - 8);

        $dbYears = Purchase::where('status', 1)
            ->whereNotNull('date')
            ->selectRaw('DISTINCT YEAR(date) as yr')
            ->pluck('yr')
            ->map(fn($y) => (int) $y)
            ->toArray();

        $availableYears = array_unique(array_merge($rangeYears, $dbYears, [$year, $benchmarkYear]));
        rsort($availableYears);

        $latestPdf = $this->getLatestReportFile('purchase_vs_last_year_report', 'admin.purchase-reports.vs-last-year.pdf.download');
        $isCustomFilter = ($year !== $currentYear) || ($benchmarkYear !== ($currentYear - 1));

        return view('backend.purchase_report.value_vs_last_year', compact('comparison', 'year', 'benchmarkYear', 'availableYears', 'latestPdf', 'isCustomFilter'));
    }

    /**
     * Async PDF Generation Dispatch for Purchase Value vs Last Year
     */
    public function vsLastYearPdfAsync(Request $request): JsonResponse
    {
        $year = (int) $request->query('year', now()->year);
        $benchmarkYear = $request->filled('benchmark_year') ? (int) $request->query('benchmark_year') : null;
        GeneratePurchaseVsLastYearPdfJob::dispatch($year, auth()->id() ?? 0, $benchmarkYear);

        return response()->json([
            'status'        => 'success',
            'message'       => 'Purchase vs Last Year PDF generation started in background.',
            'dispatched_at' => time(),
        ]);
    }

    /**
     * PR Received / Pending / Items Pending Reports
     */
    public function prStatus(PrStatusDataTable $dataTable, Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'department_id', 'status']);
        $prData = $this->reportService->getPrStatusReport($filters);
        $departments = \App\Models\Department::where('status', 1)->orderBy('name')->get();

        return $dataTable->render('backend.purchase_report.pr_status', compact('prData', 'filters', 'departments'));
    }

    /**
     * Items Purchased & PO Issued List
     */
    public function poStatus(PoStatusDataTable $dataTable, Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'vendor_id', 'purchase_type', 'milestone_status']);
        $vendors = Vendor::where('status', 1)->orderBy('shop_name')->get();

        return $dataTable->render('backend.purchase_report.po_status', compact('filters', 'vendors'));
    }
}
