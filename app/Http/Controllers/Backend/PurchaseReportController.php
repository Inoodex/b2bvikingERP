<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ItemWisePurchaseDataTable;
use App\DataTables\PoStatusDataTable;
use App\DataTables\PrStatusDataTable;
use App\DataTables\SupplierWisePurchaseDataTable;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Vendor;
use App\Services\PurchaseReportService;
use Illuminate\Http\Request;

class PurchaseReportController extends Controller
{
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
        $filters = $request->only(['start_date', 'end_date']);
        $result = $this->reportService->getTotalPurchaseValue($filters);
        $summary = $result['summary'];
        $reportData = $result['reportData'];

        return view('backend.purchase_report.total_value', compact('summary', 'reportData', 'filters'));
    }

    /**
     * Purchase Value vs Last Year Comparison
     */
    public function vsLastYear(Request $request)
    {
        $year = $request->query('year', now()->year);
        $comparison = $this->reportService->getPurchaseVsLastYear((int) $year);

        return view('backend.purchase_report.value_vs_last_year', compact('comparison', 'year'));
    }

    /**
     * PR Received / Pending / Items Pending Reports
     */
    public function prStatus(PrStatusDataTable $dataTable, Request $request)
    {
        $filters = $request->only(['start_date', 'end_date']);
        $prData = $this->reportService->getPrStatusReport($filters);

        return $dataTable->render('backend.purchase_report.pr_status', compact('prData', 'filters'));
    }

    /**
     * Items Purchased & PO Issued List
     */
    public function poStatus(PoStatusDataTable $dataTable, Request $request)
    {
        $filters = $request->only(['start_date', 'end_date']);

        return $dataTable->render('backend.purchase_report.po_status', compact('filters'));
    }
}
