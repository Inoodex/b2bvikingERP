<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\PoDataTable;
use App\Http\Controllers\Controller;
use App\Jobs\GeneratePoPdfJob;
use App\Jobs\SendPoEmailJob;
use App\Models\ComparisonStatement;
use App\Models\GeneralSetting;
use App\Models\Purchase;
use App\Services\ApprovalService;
use App\Services\PurchaseOrderService;
use App\Support\PdfCacheManager;
use App\Support\PdfImageHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    protected PurchaseOrderService $purchaseOrderService;
    protected ApprovalService $approvalService;

    public function __construct(PurchaseOrderService $purchaseOrderService, ApprovalService $approvalService)
    {
        $this->purchaseOrderService = $purchaseOrderService;
        $this->approvalService = $approvalService;
    }

    public function index(PoDataTable $dataTable)
    {
        return $dataTable->render('backend.purchase.po_list');
    }

    public function generateFromCs($csId): RedirectResponse
    {
        if (!$this->approvalService->canUserInitiateDocument(Purchase::class)) {
            Toastr::error('You are not authorized to generate Purchase Orders under the current active workflow.');
            return redirect()->back();
        }

        $cs = ComparisonStatement::with(['rfq', 'items.selectedQuotationItem.quotation'])->findOrFail($csId);
        $generatedPos = $this->purchaseOrderService->generateFromComparisonStatement($cs, Auth::id() ?? 1);

        foreach ($generatedPos as $po) {
            $this->approvalService->submitForApproval($po, (float)$po->total_amount);
        }

        if ($cs->rfq) {
            $cs->rfq->update(['status' => 'closed']);
        }

        Toastr::success(count($generatedPos) . ' Purchase Order(s) generated successfully!');
        return redirect()->route('admin.purchase-orders.index');
    }

    public function show($id): View
    {
        $po = Purchase::with(['vendor', 'rfq', 'comparisonStatement', 'currency', 'items.product', 'items.variant', 'proformaInvoice', 'letterOfCredit.expenses', 'letterOfCredit.amendments', 'emailLogs'])->findOrFail($id);
        return view('backend.purchase.po_show', compact('po'));
    }

    public function approve($id): RedirectResponse
    {
        $po = Purchase::findOrFail($id);
        $success = $this->approvalService->approveStep($po, (int)(Auth::id() ?? 1));

        if ($success) {
            Toastr::success('Purchase Order Approved Successfully!');
        } else {
            Toastr::error('Failed or unauthorized to approve Purchase Order.');
        }

        return redirect()->back();
    }

    public function cancel($id): RedirectResponse
    {
        $po = Purchase::findOrFail($id);
        $po->update([
            'milestone_status' => 'cancelled',
            'approval_status'  => 'rejected',
        ]);

        Toastr::warning('Purchase Order ' . ($po->po_no ?? $po->id) . ' has been cancelled.');
        return redirect()->back();
    }

    public function sendEmail($id): RedirectResponse
    {
        $po = Purchase::with('vendor')->findOrFail($id);
        if (!$po->vendor || !$po->vendor->email) {
            Toastr::error('Vendor email address is missing.');
            return redirect()->back();
        }

        SendPoEmailJob::dispatch($po, $po->vendor->email, 'PO Email Notification to Supplier');
        $po->update(['milestone_status' => 'po_sent']);

        Toastr::success('PO Email job has been dispatched to background queue!');
        return redirect()->back();
    }

    public function streamPdf(int $id)
    {
        $path = 'pos/po_' . $id . '.pdf';

        if (PdfCacheManager::isFresh($path, 3600)) {
            return response()->file(Storage::disk('public')->path($path), [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="PO-' . $id . '.pdf"',
            ]);
        }

        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $po = Purchase::with(['vendor', 'currency', 'items.product', 'items.variant'])->findOrFail($id);
        $settings = GeneralSetting::first();
        if ($settings && $settings->site_logo) {
            $settings->optimized_logo = PdfImageHelper::optimize($settings->site_logo, 480, 120, 95);
        }

        $pdf = Pdf::setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => false,
            'defaultFont'          => 'sans-serif',
        ])->loadView('backend.purchase.po_pdf', compact('po', 'settings'));

        Storage::disk('public')->put($path, $pdf->output());
        GeneratePoPdfJob::dispatch($id, Auth::id() ?? 1);

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="PO-' . $id . '.pdf"',
        ]);
    }

    public function downloadPdf(int $id)
    {
        $path = 'pos/po_' . $id . '.pdf';

        if (!PdfCacheManager::isFresh($path, 3600)) {
            GeneratePoPdfJob::dispatch($id, Auth::id() ?? 1);
            Toastr::info('PO PDF is generating in the background. You will be notified once ready.');
            return redirect()->back();
        }

        return Storage::disk('public')->download($path, 'PO-' . $id . '.pdf');
    }
}
