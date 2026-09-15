<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Company;
use App\Models\GeneralSetting;
use App\Models\OrderPayment;
use App\Models\PurchasePayment;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Models\Vendor;
use App\Services\VendorLedgerService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class GeneratePayablesReceivablesReportPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $reportType;
    public array $filters;
    public int $userId;
    public int $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(string $reportType, array $filters, int $userId)
    {
        $this->reportType = $reportType;
        $this->filters = $filters;
        $this->userId = $userId;
    }

    /**
     * Execute the background PDF generation job.
     */
    public function handle(): void
    {
        ini_set('memory_limit', '512M');
        set_time_limit(600);

        $company = Company::first();
        $settings = GeneralSetting::first();
        $user = User::find($this->userId);
        $generatedBy = $user?->name ?? 'Administrator';

        $tempDir = storage_path('app/temp_reports');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        switch ($this->reportType) {
            case 'ar_aging':
                $this->generateArAging($company, $settings, $tempDir, $generatedBy);
                break;
            case 'customer_ledger':
                $this->generateCustomerLedger($company, $settings, $tempDir, $generatedBy);
                break;
            case 'vendor_payments':
                $this->generateVendorPayments($company, $settings, $tempDir, $generatedBy);
                break;
            case 'ap_aging':
                $this->generateApAging($company, $settings, $tempDir, $generatedBy);
                break;
            case 'supplier_statement':
                $this->generateSupplierStatement($company, $settings, $tempDir, $generatedBy);
                break;
            default:
                Log::error("Unknown payables/receivables report type: {$this->reportType}");
                break;
        }
    }

    /**
     * 1. Generate Customer AR Aging PDF (Accounts Receivable)
     */
    private function generateArAging(?Company $company, ?GeneralSetting $settings, string $tempDir, string $generatedBy): void
    {
        $customerId = $this->filters['customer_id'] ?? null;

        $query = SalesInvoice::with(['order.user'])
            ->where('due_amount', '>', 0)
            ->where('status', 'posted');

        if ($customerId) {
            $query->whereHas('order', function ($q) use ($customerId) {
                $q->where('user_id', $customerId);
            });
        }

        $invoices = $query->get();
        $agingData = [];
        $totals = [
            'total_due'    => 0.00,
            'current_0_30' => 0.00,
            'days_31_60'   => 0.00,
            'days_61_90'   => 0.00,
            'over_90'      => 0.00,
        ];

        $now = Carbon::now();

        foreach ($invoices as $inv) {
            $u = $inv->order ? $inv->order->user : null;
            $uId = $u ? $u->id : 0;
            $customerName = $u ? ($u->outlet_name ? $u->outlet_name . ' (' . $u->name . ')' : $u->name) : 'Guest / Unassigned';
            $phone = $u ? ($u->phone ?: 'N/A') : 'N/A';

            if (!isset($agingData[$uId])) {
                $agingData[$uId] = [
                    'customer_id'   => $uId,
                    'customer_name' => $customerName,
                    'phone'         => $phone,
                    'total_due'     => 0.00,
                    'current_0_30'  => 0.00,
                    'days_31_60'    => 0.00,
                    'days_61_90'    => 0.00,
                    'over_90'       => 0.00,
                    'invoice_count' => 0,
                ];
            }

            $invDate = Carbon::parse($inv->created_at);
            $ageInDays = $invDate->diffInDays($now);
            $due = (float) $inv->due_amount;

            $agingData[$uId]['total_due'] += $due;
            $agingData[$uId]['invoice_count'] += 1;
            $totals['total_due'] += $due;

            if ($ageInDays <= 30) {
                $agingData[$uId]['current_0_30'] += $due;
                $totals['current_0_30'] += $due;
            } elseif ($ageInDays <= 60) {
                $agingData[$uId]['days_31_60'] += $due;
                $totals['days_31_60'] += $due;
            } elseif ($ageInDays <= 90) {
                $agingData[$uId]['days_61_90'] += $due;
                $totals['days_61_90'] += $due;
            } else {
                $agingData[$uId]['over_90'] += $due;
                $totals['over_90'] += $due;
            }
        }

        $data = [
            'agingData'      => $agingData,
            'totals'         => $totals,
            'company'        => $company,
            'settings'       => $settings,
            'generalSetting' => $settings,
            'generatedBy'    => $generatedBy,
            'generatedAt'    => now(),
        ];

        $this->saveAndNotify(
            'backend.pdf.ar_aging',
            $data,
            'ar_aging',
            'Customer AR Aging Report',
            'Accounts Receivable Aging Portfolio Matrix',
            $tempDir,
            'a4',
            'landscape'
        );
    }

    /**
     * 2. Generate Customer Payment Ledger PDF (Order Payments)
     */
    private function generateCustomerLedger(?Company $company, ?GeneralSetting $settings, string $tempDir, string $generatedBy): void
    {
        $startDate = $this->filters['start_date'] ?? null;
        $endDate   = $this->filters['end_date'] ?? null;
        $method    = $this->filters['method'] ?? null;
        $search    = trim((string) ($this->filters['search'] ?? ''));

        $query = OrderPayment::query()->with(['order', 'receipts']);

        if (!empty($startDate)) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        if (!empty($method)) {
            $query->where('payment_method', $method);
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', '%' . $search . '%')
                    ->orWhere('payment_method', 'like', '%' . $search . '%')
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('order_no', 'like', '%' . $search . '%')
                            ->orWhere('billing_name', 'like', '%' . $search . '%')
                            ->orWhere('billing_phone', 'like', '%' . $search . '%');
                    });
            });
        }

        $payments = $query->orderByDesc('id')->get();

        $summary = [
            'count'        => $payments->count(),
            'total_amount' => (float) $payments->sum('amount'),
        ];

        $filters = [
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'method'     => $method,
            'search'     => $search,
        ];

        $data = [
            'payments'    => $payments,
            'company'     => $company,
            'settings'    => $settings,
            'filters'     => $filters,
            'summary'     => $summary,
            'generatedBy' => $generatedBy,
            'generatedAt' => now(),
        ];

        $this->saveAndNotify(
            'backend.accounts.payment_history_pdf',
            $data,
            'customer_ledger',
            'Customer Payment Ledger',
            'Total Transactions: ' . number_format($summary['count']) . ' | Amount: kr. ' . number_format($summary['total_amount'], 2),
            $tempDir,
            'a4',
            'portrait'
        );
    }

    /**
     * 3. Generate Vendor Payment History PDF (Purchase Payments)
     */
    private function generateVendorPayments(?Company $company, ?GeneralSetting $settings, string $tempDir, string $generatedBy): void
    {
        $vendorId  = $this->filters['vendor_id'] ?? null;
        $startDate = $this->filters['start_date'] ?? null;
        $endDate   = $this->filters['end_date'] ?? null;
        $method    = $this->filters['method'] ?? null;
        $search    = trim((string) ($this->filters['search'] ?? ''));

        $query = PurchasePayment::query()->with(['purchase.vendor', 'receipts']);

        if (!empty($vendorId)) {
            $query->where('vendor_id', (int) $vendorId);
        }
        if (!empty($startDate)) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        if (!empty($method)) {
            $query->where('payment_method', $method);
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', '%' . $search . '%')
                    ->orWhere('note', 'like', '%' . $search . '%')
                    ->orWhereHas('purchase', function ($pq) use ($search) {
                        $pq->where('invoice_no', 'like', '%' . $search . '%')
                            ->orWhereHas('vendor', function ($vq) use ($search) {
                                $vq->where('shop_name', 'like', '%' . $search . '%')
                                    ->orWhere('phone', 'like', '%' . $search . '%');
                            });
                    });
            });
        }

        $payments = $query->orderByDesc('id')->get();

        $vendorName = !empty($vendorId) ? Vendor::whereKey((int) $vendorId)->value('shop_name') : null;

        $summary = [
            'count'        => $payments->count(),
            'total_amount' => (float) $payments->sum('amount'),
        ];

        $filters = [
            'vendor_id'   => $vendorId,
            'vendor_name' => $vendorName,
            'start_date'  => $startDate,
            'end_date'    => $endDate,
            'method'      => $method,
            'search'      => $search,
        ];

        $data = [
            'payments'    => $payments,
            'company'     => $company,
            'settings'    => $settings,
            'filters'     => $filters,
            'summary'     => $summary,
            'generatedBy' => $generatedBy,
            'generatedAt' => now(),
        ];

        $this->saveAndNotify(
            'backend.accounts.vendor_payment_history_pdf',
            $data,
            'vendor_payments',
            'Vendor Payment History',
            'Total Records: ' . number_format($summary['count']) . ' | Amount: kr. ' . number_format($summary['total_amount'], 2),
            $tempDir,
            'a4',
            'portrait'
        );
    }

    /**
     * 4. Generate AP Vendor Aging PDF (Accounts Payable)
     */
    private function generateApAging(?Company $company, ?GeneralSetting $settings, string $tempDir, string $generatedBy): void
    {
        $vendorId = !empty($this->filters['vendor_id']) ? (int) $this->filters['vendor_id'] : null;
        $ledgerService = app(VendorLedgerService::class);
        $agingData = $ledgerService->getAgingReport($vendorId);

        $totals = [
            'current'      => (float) $agingData->sum('current'),
            'days_31_60'   => (float) $agingData->sum('days_31_60'),
            'days_61_90'   => (float) $agingData->sum('days_61_90'),
            'days_90_plus' => (float) $agingData->sum('days_90_plus'),
            'total_due'    => (float) $agingData->sum('total_due'),
        ];

        $data = [
            'agingData'   => $agingData,
            'totals'      => $totals,
            'company'     => $company,
            'settings'    => $settings,
            'generatedBy' => $generatedBy,
            'generatedAt' => now(),
        ];

        $this->saveAndNotify(
            'backend.vendor_ledger.aging_pdf',
            $data,
            'ap_aging',
            'AP Vendor Aging Report',
            'Accounts Payable Aging Portfolio Matrix',
            $tempDir,
            'a4',
            'landscape'
        );
    }

    /**
     * 5. Generate Supplier Statement of Account PDF
     */
    private function generateSupplierStatement(?Company $company, ?GeneralSetting $settings, string $tempDir, string $generatedBy): void
    {
        $vendorId = (int) ($this->filters['vendor_id'] ?? 0);
        $vendor = Vendor::findOrFail($vendorId);
        $fromDate = $this->filters['from_date'] ?? null;
        $toDate   = $this->filters['to_date'] ?? null;

        $ledgerService = app(VendorLedgerService::class);
        $statement = $ledgerService->getVendorStatement($vendor, $fromDate, $toDate);

        $filePrefix = "supplier_statement_{$vendorId}";

        $data = [
            'statement'   => $statement,
            'vendor'      => $vendor,
            'fromDate'    => $fromDate,
            'toDate'      => $toDate,
            'company'     => $company,
            'settings'    => $settings,
            'generatedBy' => $generatedBy,
            'generatedAt' => now(),
        ];

        $this->saveAndNotify(
            'backend.vendor_ledger.statement_pdf',
            $data,
            $filePrefix,
            'Statement: ' . ($vendor->shop_name ?? $vendor->name),
            'Outstanding: kr. ' . number_format($statement['outstanding_balance'], 2),
            $tempDir,
            'a4',
            'portrait'
        );
    }

    /**
     * Purge old files for this user, render DomPDF, save to disk, and register bell notification.
     */
    private function saveAndNotify(
        string $view,
        array $data,
        string $filePrefix,
        string $title,
        string $desc,
        string $tempDir,
        string $paper = 'a4',
        string $orientation = 'portrait'
    ): void {
        // 1. Dynamic Purge: Delete previous temporary files for this user & prefix
        $pattern = $tempDir . "/{$filePrefix}_u{$this->userId}_*.pdf";
        foreach (glob($pattern) as $oldFile) {
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        // 2. Generate new PDF with live data
        $timestamp = now()->format('Ymd_His');
        $storedFileName = "{$filePrefix}_u{$this->userId}_{$timestamp}.pdf";
        $filePath = "{$tempDir}/{$storedFileName}";

        $pdf = Pdf::loadView($view, $data)->setPaper($paper, $orientation);
        $pdf->save($filePath);

        // 3. Add to user notification cache (notification bell icon shows PDF ready)
        $this->addCacheNotification([
            'type'      => 'pdf_ready',
            'title'     => $title,
            'desc'      => $desc,
            'url'       => route('admin.reports.payables-receivables.download', ['file' => $storedFileName]),
            'icon'      => 'fas fa-file-pdf',
            'class'     => 'bg-success',
            'timestamp' => now()->timestamp,
        ]);
    }

    /**
     * Push PDF notification to user notification cache.
     */
    private function addCacheNotification(array $data): void
    {
        $key = 'user_pdf_notifications_' . $this->userId;
        $notifications = Cache::get($key, []);
        $data['time'] = now()->diffForHumans();
        $data['is_unread'] = true;
        $data['is_out_of_stock'] = false;
        $notifications[] = $data;
        $notifications = array_slice($notifications, -20);
        Cache::put($key, $notifications, now()->addDays(7));
    }
}
