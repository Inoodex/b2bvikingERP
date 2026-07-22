<?php

namespace App\Jobs;

use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\Issue;
use App\Models\IssueItem;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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

        $issueBase = DB::table('issue_items')
            ->join('issues', 'issue_items.issue_id', '=', 'issues.id')
            ->leftJoin('orders', 'issues.order_id', '=', 'orders.id')
            ->where(function ($q) {
                $q->whereNotNull('issues.order_id')->where('orders.status', 'completed');
                if (!empty($this->filters['user_id'])) $q->where('orders.user_id', $this->filters['user_id']);
                $q->orWhereNull('issues.order_id');
                if (!empty($this->filters['user_id'])) $q->where('issues.outlet_id', $this->filters['user_id']);
            });

        if ($hasDateFilter) {
            $totalRevenue = $summary->total_value;
        } else {
            $totalRevenue = (clone $issueBase)->sum(\Illuminate\Support\Facades\DB::raw('issue_items.quantity * COALESCE(issue_items.unit_price, 0)'));
        }

        $issueStats = Issue::leftJoin('issue_items', 'issues.id', '=', 'issue_items.issue_id')
            ->where(function ($q) {
                if (!empty($this->filters['user_id'])) $q->where('issues.outlet_id', $this->filters['user_id']);
                if (!empty($this->filters['date_from'])) $q->whereDate('issues.created_at', '>=', $this->filters['date_from']);
                if (!empty($this->filters['date_to'])) $q->whereDate('issues.created_at', '<=', $this->filters['date_to']);
                if (!empty($this->filters['month'])) $q->whereMonth('issues.created_at', $this->filters['month']);
                if (!empty($this->filters['year'])) $q->whereYear('issues.created_at', $this->filters['year']);
            })
            ->selectRaw('COUNT(DISTINCT issues.id) as total_issues, COALESCE(SUM(issue_items.quantity),0) as total_issued_qty')
            ->first();

        $settings = GeneralSetting::first();

        $data = [];

        if (!empty($this->filters['user_id'])) {
            $user = User::find($this->filters['user_id']);

            $paymentStats = OrderPayment::whereIn('order_id', $orderIds)
                ->selectRaw('COALESCE(SUM(amount),0) as total_paid')->first();
            $totalDue = $summary->total_value - $paymentStats->total_paid;

            $linkedValue = IssueItem::whereHas('issue', function ($q) use ($orderIds) {
                    $q->whereIn('order_id', $orderIds);
                    if (!empty($this->filters['date_from'])) $q->whereDate('issues.created_at', '>=', $this->filters['date_from']);
                    if (!empty($this->filters['date_to'])) $q->whereDate('issues.created_at', '<=', $this->filters['date_to']);
                    if (!empty($this->filters['month'])) $q->whereMonth('issues.created_at', $this->filters['month']);
                    if (!empty($this->filters['year'])) $q->whereYear('issues.created_at', $this->filters['year']);
                })
                ->sum(DB::raw('issue_items.quantity * COALESCE(issue_items.unit_price, 0)'));

            $standaloneValue = IssueItem::whereHas('issue', function ($q) {
                    $q->whereNull('order_id')->where('outlet_id', $this->filters['user_id']);
                    if (!empty($this->filters['date_from'])) $q->whereDate('issues.created_at', '>=', $this->filters['date_from']);
                    if (!empty($this->filters['date_to'])) $q->whereDate('issues.created_at', '<=', $this->filters['date_to']);
                    if (!empty($this->filters['month'])) $q->whereMonth('issues.created_at', $this->filters['month']);
                    if (!empty($this->filters['year'])) $q->whereYear('issues.created_at', $this->filters['year']);
                })
                ->sum(DB::raw('issue_items.quantity * COALESCE(issue_items.unit_price, 0)'));

            $issueValue = $linkedValue + $standaloneValue;
            $pendingValue = max(0, $summary->total_value - $linkedValue);

            $orders = $query->with('items')->orderByDesc('placed_at')->get();

            $issues = Issue::with(['items', 'order'])
                ->where(function ($q) use ($orderIds) {
                    $q->whereIn('order_id', $orderIds);
                    if (!empty($this->filters['date_from'])) $q->whereDate('issues.created_at', '>=', $this->filters['date_from']);
                    if (!empty($this->filters['date_to'])) $q->whereDate('issues.created_at', '<=', $this->filters['date_to']);
                    if (!empty($this->filters['month'])) $q->whereMonth('issues.created_at', $this->filters['month']);
                    if (!empty($this->filters['year'])) $q->whereYear('issues.created_at', $this->filters['year']);
                    $q->orWhere(function ($sq) {
                        $sq->whereNull('order_id');
                        if (!empty($this->filters['user_id'])) $sq->where('outlet_id', $this->filters['user_id']);
                        if (!empty($this->filters['date_from'])) $sq->whereDate('issues.created_at', '>=', $this->filters['date_from']);
                        if (!empty($this->filters['date_to'])) $sq->whereDate('issues.created_at', '<=', $this->filters['date_to']);
                        if (!empty($this->filters['month'])) $sq->whereMonth('issues.created_at', $this->filters['month']);
                        if (!empty($this->filters['year'])) $sq->whereYear('issues.created_at', $this->filters['year']);
                    });
                })
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($issue) {
                    $value = 0;
                    foreach ($issue->items as $item) {
                        $value += $item->quantity * (float) ($item->unit_price ?? 0);
                    }
                    $issue->computed_value = $value;
                    return $issue;
                });

            $payments = OrderPayment::with('order')
                ->whereIn('order_id', $orderIds)
                ->orderByDesc('created_at')
                ->get();

            $productComparison = OrderItem::whereIn('order_id', $orderIds)
                ->selectRaw('product_id, product_name, SUM(quantity) as ordered_qty, COALESCE(SUM(line_total),0) as ordered_value')
                ->groupBy('product_id', 'product_name')
                ->orderByDesc('ordered_value')
                ->get()
                ->map(function ($item) use ($orderIds) {
                    $issuedQty = IssueItem::whereHas('issue', fn($q) => $q->whereIn('order_id', $orderIds))
                        ->where('product_id', $item->product_id)->sum('quantity');
                    $item->issued_qty = (int) $issuedQty;
                    $item->pending_qty = max(0, $item->ordered_qty - $item->issued_qty);
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

            $issueValue = Issue::leftJoin('issue_items', 'issues.id', '=', 'issue_items.issue_id')
                ->where(function ($q) {
                    if (!empty($this->filters['date_from'])) $q->whereDate('issues.created_at', '>=', $this->filters['date_from']);
                    if (!empty($this->filters['date_to'])) $q->whereDate('issues.created_at', '<=', $this->filters['date_to']);
                    if (!empty($this->filters['month'])) $q->whereMonth('issues.created_at', $this->filters['month']);
                    if (!empty($this->filters['year'])) $q->whereYear('issues.created_at', $this->filters['year']);
                })
                ->sum(DB::raw('issue_items.quantity * COALESCE(issue_items.unit_price, 0)'));

            $userSummary = Issue::leftJoin('issue_items', 'issues.id', '=', 'issue_items.issue_id')
                ->where(function ($q) {
                    if (!empty($this->filters['date_from'])) $q->whereDate('issues.created_at', '>=', $this->filters['date_from']);
                    if (!empty($this->filters['date_to'])) $q->whereDate('issues.created_at', '<=', $this->filters['date_to']);
                    if (!empty($this->filters['month'])) $q->whereMonth('issues.created_at', $this->filters['month']);
                    if (!empty($this->filters['year'])) $q->whereYear('issues.created_at', $this->filters['year']);
                })
                ->selectRaw('COALESCE(outlet_id, 0) as user_id, COUNT(DISTINCT issues.id) as total_orders, COALESCE(SUM(issue_items.quantity * COALESCE(issue_items.unit_price, 0)),0) as total_value, COALESCE(SUM(issue_items.quantity),0) as total_qty')
                ->groupBy('outlet_id')
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

        $userName = 'All_Users';
        if (!empty($this->filters['user_id'])) {
            $u = User::find($this->filters['user_id']);
            $userName = $u ? preg_replace('/[^a-zA-Z0-9_\-]/', '_', $u->name) : 'User_' . $this->filters['user_id'];
        }
        $filterPart = '';
        if (!empty($this->filters['month'])) {
            $filterPart .= date('M', mktime(0, 0, 0, $this->filters['month'], 1));
        }
        if (!empty($this->filters['year'])) {
            $filterPart .= $this->filters['year'];
        }
        if (!empty($this->filters['date_from']) || !empty($this->filters['date_to'])) {
            $filterPart .= ($this->filters['date_from'] ?? '') . '_' . ($this->filters['date_to'] ?? '');
        }
        $filterPart = $filterPart ? '_' . $filterPart : '';
        $filename = $userName . $filterPart . '_' . now()->format('Ymd_His') . '.pdf';
        $path = 'reports/' . $filename;
        Storage::disk('public')->put($path, $pdf->output());

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
            'title' => 'Report Ready',
            'desc' => "Order & Issue Report ({$filterLabel}) is ready.",
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
