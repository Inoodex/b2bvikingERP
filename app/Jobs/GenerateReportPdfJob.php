<?php

namespace App\Jobs;

use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class GenerateReportPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $filters;
    public $userId;
    public $timeout = 3600;

    public function __construct(array $filters, $userId)
    {
        $this->filters = $filters;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $query = Order::with(['user', 'items.product'])->where('status', 'completed');

        if (!empty($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        }
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('placed_at', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('placed_at', '<=', $this->filters['date_to']);
        }
        if (!empty($this->filters['month'])) {
            $query->whereMonth('placed_at', $this->filters['month']);
        }
        if (!empty($this->filters['year'])) {
            $query->whereYear('placed_at', $this->filters['year']);
        }

        $orderIds = (clone $query)->pluck('id');

        $summary = (clone $query)->selectRaw('
            COUNT(*) as total_orders,
            COALESCE(SUM(total_amount),0) as total_value,
            COALESCE(AVG(total_amount),0) as avg_order_value
        ')->first();

        $hasDateFilter = !empty($this->filters['month']) || !empty($this->filters['year']) || !empty($this->filters['date_from']) || !empty($this->filters['date_to']);

        $totalRevenue = (float) ($summary->total_value ?? 0);
        $issueStats = (object)['total_issues' => 0, 'total_issued_qty' => 0];

        $settings = GeneralSetting::first();

        $data = [];

        if (!empty($this->filters['user_id'])) {
            $user = User::find($this->filters['user_id']);

            $paymentStats = OrderPayment::whereIn('order_id', $orderIds)
                ->selectRaw('COALESCE(SUM(amount),0) as total_paid')->first();
            $issueValue = 0;
            $pendingValue = 0;

            $orders = $query->with('items')->orderByDesc('placed_at')->get();
            $issues = collect();

            $payments = OrderPayment::with('order')
                ->whereIn('order_id', $orderIds)
                ->orderByDesc('created_at')
                ->get();

            $productComparison = OrderItem::whereIn('order_id', $orderIds)
                ->selectRaw('product_id, product_name, SUM(quantity) as ordered_qty, COALESCE(SUM(line_total),0) as ordered_value')
                ->groupBy('product_id', 'product_name')
                ->orderByDesc('ordered_value')
                ->get()
                ->map(function ($item) {
                    $item->issued_qty = (int) $item->ordered_qty;
                    $item->pending_qty = 0;
                    return $item;
                });

            $monthlyTrend = Order::where('status', 'completed')
                ->when(!empty($this->filters['user_id']), fn($q) => $q->where('user_id', $this->filters['user_id']))
                ->when(!empty($this->filters['date_from']), fn($q) => $q->whereDate('placed_at', '>=', $this->filters['date_from']))
                ->when(!empty($this->filters['date_to']), fn($q) => $q->whereDate('placed_at', '<=', $this->filters['date_to']))
                ->when(!empty($this->filters['month']), fn($q) => $q->whereMonth('placed_at', $this->filters['month']))
                ->when(!empty($this->filters['year']), fn($q) => $q->whereYear('placed_at', $this->filters['year']))
                ->selectRaw("DATE_FORMAT(placed_at, '%Y-%m') as month, COUNT(*) as orders_count, COALESCE(SUM(total_amount),0) as total_amount")
                ->groupBy('month')->orderBy('month', 'desc')
                ->get();

            $data = compact(
                'user', 'summary', 'issueStats', 'paymentStats', 'totalDue', 'issueValue', 'pendingValue',
                'orders', 'issues', 'payments', 'productComparison', 'monthlyTrend', 'settings', 'totalRevenue', 'orderIds'
            );
        } else {
            $productFrequency = OrderItem::whereIn('order_id', $orderIds)
                ->selectRaw('product_id, product_name, COUNT(*) as times_ordered, SUM(quantity) as total_qty, COALESCE(SUM(line_total),0) as total_value')
                ->groupBy('product_id', 'product_name')
                ->orderByDesc('times_ordered')
                ->get();

            $monthlyTrend = Order::where('status', 'completed')
                ->when(!empty($this->filters['user_id']), fn($q) => $q->where('user_id', $this->filters['user_id']))
                ->when(!empty($this->filters['date_from']), fn($q) => $q->whereDate('placed_at', '>=', $this->filters['date_from']))
                ->when(!empty($this->filters['date_to']), fn($q) => $q->whereDate('placed_at', '<=', $this->filters['date_to']))
                ->when(!empty($this->filters['month']), fn($q) => $q->whereMonth('placed_at', $this->filters['month']))
                ->when(!empty($this->filters['year']), fn($q) => $q->whereYear('placed_at', $this->filters['year']))
                ->selectRaw("DATE_FORMAT(placed_at, '%Y-%m') as month, COUNT(*) as orders_count, COALESCE(SUM(total_amount),0) as total_amount")
                ->groupBy('month')->orderBy('month', 'desc')
                ->get();

            $issueValue = $totalRevenue;

            $userSummary = DB::table('orders')
                ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.status', 'completed')
                ->whereIn('orders.id', $orderIds)
                ->selectRaw('orders.user_id, COUNT(DISTINCT orders.id) as total_orders, COALESCE(SUM(orders.total_amount),0) as total_value, COALESCE(SUM(order_items.quantity),0) as total_qty')
                ->groupBy('orders.user_id')
                ->orderByDesc('total_value')
                ->get()
                ->keyBy('user_id');

            $data = compact(
                'summary', 'issueStats', 'productFrequency', 'monthlyTrend', 'userSummary', 'settings', 'orderIds', 'issueValue', 'totalRevenue'
            );
        }

        $request = $this->filters;
        $data['request'] = $request;

        $pdf = Pdf::loadView('backend.reports.orders_pdf', $data)->setPaper('a4', 'landscape');

        $tempDir = storage_path('app/temp_reports');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        // Pre-generation purge: delete previous order_sales_report PDFs for this user
        $pattern = $tempDir . "/order_sales_report_u{$this->userId}_*.pdf";
        foreach (glob($pattern) as $oldFile) {
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        $timestamp = now()->format('Ymd_His');
        $filename = "order_sales_report_u{$this->userId}_{$timestamp}.pdf";
        $filePath = "{$tempDir}/{$filename}";
        $pdf->save($filePath);

        $filterLabel = '';
        if (!empty($this->filters['user_id'])) {
            $filterLabel .= ' User#' . $this->filters['user_id'];
        }
        if (!empty($this->filters['month'])) {
            $filterLabel .= ' ' . date('F', mktime(0, 0, 0, $this->filters['month'], 1));
        }
        if (!empty($this->filters['year'])) {
            $filterLabel .= ' ' . $this->filters['year'];
        }
        if (!empty($this->filters['date_from']) || !empty($this->filters['date_to'])) {
            $filterLabel .= ' ' . ($this->filters['date_from'] ?? '') . '→' . ($this->filters['date_to'] ?? '');
        }
        $filterLabel = trim($filterLabel) ?: 'All';

        $this->addCacheNotification($this->userId, [
            'type' => 'pdf_ready',
            'title' => 'Order & Sales Report Ready',
            'desc' => "Order & Sales Report ({$filterLabel}) is ready.",
            'url' => route('admin.reports.orders.pdf.download', ['file' => $filename]),
            'icon' => 'fas fa-file-pdf',
            'class' => 'bg-success',
            'timestamp' => now()->timestamp,
        ]);
    }

    private function addCacheNotification($userId, $data)
    {
        $key = 'user_pdf_notifications_' . $userId;
        $notifications = Cache::get($key, []);
        $data['time'] = now()->diffForHumans();
        $data['is_unread'] = true;
        $data['is_out_of_stock'] = false;
        $notifications[] = $data;
        $notifications = array_slice($notifications, -20);
        Cache::put($key, $notifications, now()->addDays(7));
    }
}
