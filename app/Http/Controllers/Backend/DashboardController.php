<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Approval;
use App\Models\ChartOfAccount;
use App\Models\DeliveryOrder;
use App\Models\GoodsReceipt;
use App\Models\InventoryStock;
use App\Models\JournalEntryLine;
use App\Models\LetterOfCredit;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\SalesInvoice;
use App\Models\StockTransfer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // -------------------------------------------------------------------------
        // Executive Scope Controls: Period & Outlet
        // -------------------------------------------------------------------------
        $selectedPeriod = $request->query('period', 'all');
        $selectedOutletId = $request->query('outlet_id');

        $startDate = match ($selectedPeriod) {
            'today' => now()->startOfDay(),
            'this_week' => now()->startOfWeek(),
            'this_month' => now()->startOfMonth(),
            'this_quarter' => now()->firstOfQuarter(),
            'ytd' => now()->startOfYear(),
            default => null,
        };

        $outlets = Outlet::where('status', 1)->orderBy('name')->get();
        if ($outlets->isEmpty()) {
            $outlets = User::role(['Outlet User', 'User'])->where('status', 1)->orderBy('name')->get(['id', 'name', 'outlet_name']);
        }

        // Outlet user filter if specific outlet selected
        $outletUserIds = null;
        if ($selectedOutletId) {
            $outletUserIds = User::where('outlet_id', $selectedOutletId)->pluck('id');
        }

        // =========================================================================
        // 1. CORE EXECUTIVE FINANCIAL HEALTH METRICS (General Ledger Aligned with Profit & Loss)
        // =========================================================================
        $revQuery = JournalEntryLine::whereHas('account', fn($q) => $q->where('account_type', 'revenue'));
        $expQuery = JournalEntryLine::whereHas('account', fn($q) => $q->where('account_type', 'expense'));
        if ($startDate) {
            $revQuery->whereHas('journalEntry', fn($q) => $q->whereDate('entry_date', '>=', $startDate));
            $expQuery->whereHas('journalEntry', fn($q) => $q->whereDate('entry_date', '>=', $startDate));
        }
        $glRevenue = (float) ($revQuery->sum('credit') - $revQuery->sum('debit'));
        $glExpense = (float) ($expQuery->sum('debit') - $expQuery->sum('credit'));

        if ($glRevenue > 0) {
            $totalRevenue = $glRevenue;
            $cogs = $glExpense;
        } else {
            $invoiceQuery = SalesInvoice::whereIn('status', ['posted', 'paid']);
            $orderQuery = Order::whereIn('status', ['approved', 'processing', 'completed']);
            if ($startDate) {
                $invoiceQuery->where('created_at', '>=', $startDate);
                $orderQuery->where('created_at', '>=', $startDate);
            }
            if ($outletUserIds && $outletUserIds->isNotEmpty()) {
                $orderQuery->whereIn('user_id', $outletUserIds);
            }
            $totalRevenue = max((float) $invoiceQuery->sum('total_amount'), (float) $orderQuery->sum('total_amount'));
            $cogs = (float) OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
                ->whereIn('order_items.order_id', Order::whereIn('status', ['completed', 'approved'])->pluck('id'))
                ->sum(DB::raw('order_items.quantity * COALESCE(NULLIF(products.purchase_price, 0), products.price * 0.6, 0)'));
        }

        // Accounts Receivable (AR) — Customer & Outlet Dues (GL 1030)
        $arQuery = Order::whereIn('status', ['approved', 'processing', 'completed']);
        if ($outletUserIds && $outletUserIds->isNotEmpty()) {
            $arQuery->whereIn('user_id', $outletUserIds);
        }
        $invoiceAR = (float) SalesInvoice::whereIn('status', ['posted', 'partial'])->sum('due_amount');
        $orderAR = (float) $arQuery->sum('due_amount');
        $accountsReceivable = max($invoiceAR, $orderAR);

        // Accounts Payable (AP) — Total Liabilities & Supplier Bills (GL 2010)
        $accountsPayable = (float) Purchase::where('status', 1)->sum('due_amount');
        if ($accountsPayable <= 0) {
            $accountsPayable = (float) Purchase::where('status', 1)->sum('total_amount');
        }

        // Total Inventory Valuation (Asset 1040 / 1050)
        $invValuationQuery = InventoryStock::join('products', 'inventory_stocks.product_id', '=', 'products.id');
        if ($selectedOutletId) {
            $invValuationQuery->where('inventory_stocks.outlet_id', $selectedOutletId);
        }
        $inventoryValuation = (float) $invValuationQuery->sum(DB::raw('inventory_stocks.quantity * COALESCE(NULLIF(products.purchase_price, 0), products.price, 0)'));

        // Gross Profit & Gross Margin % Intelligence (Strictly aligned with GL / P&L)
        $grossProfit = max(0, $totalRevenue - $cogs);
        $grossMarginPct = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 1) : 0;

        // =========================================================================
        // 2. OPERATIONAL VELOCITY KPIS & ACTION TRIGGERS
        // =========================================================================
        $ordersInPipeline = Order::whereIn('status', ['pending', 'approved', 'processing'])->count();
        $pendingApprovals = class_exists(Approval::class) ? Approval::where('status', 'pending')->count() : 0;
        if ($pendingApprovals === 0) {
            $pendingApprovals = Order::where('status', 'pending')->count();
        }

        $lowStockCount = Product::where('status', 1)
            ->withSum('inventoryStocks', 'quantity')
            ->havingRaw('inventory_stocks_sum_quantity <= 10 OR inventory_stocks_sum_quantity IS NULL')
            ->count();

        $activeOutlets = Outlet::where('status', 1)->count();
        if ($activeOutlets === 0) {
            $activeOutlets = User::role('Outlet User')->count();
        }

        $inTransitTransfers = StockTransfer::where('status', 'dispatched')->count();
        $totalCatalogProducts = Product::count();
        $totalProducts = Product::where('status', 1)->count();
        $totalActiveProducts = $totalProducts;
        $totalInactiveProducts = Product::where('status', 0)->count();
        $totalInventoryUnits = (float) InventoryStock::sum('quantity');
        $totalPurchaseOrders = Purchase::count();
        $activeLcs = LetterOfCredit::count();
        $totalGrns = GoodsReceipt::count();
        $totalDeliveryOrders = DeliveryOrder::count();
        $totalSalesInvoices = SalesInvoice::count();
        $totalIssues = $totalDeliveryOrders;
        $pendingRequests = Order::where('status', 'pending')->count();
        $totalPurchaseValue = $accountsPayable;

        // =========================================================================
        // 3. 🔥 BEST SELLER PRODUCTS (Strictly Aligned with ReportController::bestSellers)
        // =========================================================================
        $bestSellerOrderQuery = Order::where('status', 'completed');
        if ($startDate) {
            $bestSellerOrderQuery->where('placed_at', '>=', $startDate);
        }
        if ($selectedOutletId && $outletUserIds && $outletUserIds->isNotEmpty()) {
            $bestSellerOrderQuery->whereIn('user_id', $outletUserIds);
        }
        $bestSellerOrderIds = $bestSellerOrderQuery->pluck('id');
        if ($bestSellerOrderIds->isEmpty()) {
            $bestSellerOrderIds = Order::where('status', 'completed')->pluck('id');
        }

        $bestSellerProducts = OrderItem::whereIn('order_items.order_id', $bestSellerOrderIds)
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->selectRaw('
                order_items.product_id,
                order_items.product_name,
                MAX(products.thumb_image) as image,
                COALESCE(MAX(categories.name), "General") as category,
                COUNT(*) as times_ordered,
                SUM(order_items.quantity) as total_qty,
                COALESCE(SUM(order_items.line_total), 0) as total_revenue
            ')
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('times_ordered')
            ->take(5)
            ->get();

        foreach ($bestSellerProducts as $bp) {
            $bp->current_stock = (float) InventoryStock::where('product_id', $bp->product_id)->sum('quantity');
        }

        // =========================================================================
        // 4. 👑 TOP CUSTOMERS & OUTLETS (Zero-Scroll Executive Cards)
        // =========================================================================
        $topCustomers = Order::whereIn('status', ['completed', 'approved'])
            ->has('user')
            ->selectRaw('
                user_id,
                COUNT(*) as total_orders,
                COALESCE(SUM(total_amount), 0) as total_value
            ')
            ->with(['user:id,name,outlet_name,email,phone,image'])
            ->groupBy('user_id')
            ->orderByDesc('total_value')
            ->take(5)
            ->get();

        $maxSpend = $topCustomers->max('total_value') ?: 1;
        foreach ($topCustomers as $tc) {
            $tc->spend_pct = min(100, max(12, round(($tc->total_value / $maxSpend) * 100)));
            $tc->due_amount = (float) Order::where('user_id', $tc->user_id)->sum('due_amount');
        }

        // =========================================================================
        // 5. 📈 12-MONTH SALES & REVENUE GROWTH TREND (Interactive Gradient Chart)
        // =========================================================================
        $monthlySales = Order::whereIn('status', ['completed', 'approved'])
            ->selectRaw('
                DATE_FORMAT(placed_at, "%Y-%m") as month_key,
                DATE_FORMAT(placed_at, "%b %Y") as month_label,
                SUM(total_amount) as total_sales,
                COUNT(*) as total_orders
            ')
            ->whereNotNull('placed_at')
            ->where('placed_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month_key', 'month_label')
            ->orderBy('month_key', 'asc')
            ->get();

        // Fallback to created_at if placed_at timestamp was null
        if ($monthlySales->isEmpty()) {
            $monthlySales = Order::whereIn('status', ['completed', 'approved'])
                ->selectRaw('
                    DATE_FORMAT(created_at, "%Y-%m") as month_key,
                    DATE_FORMAT(created_at, "%b %Y") as month_label,
                    SUM(total_amount) as total_sales,
                    COUNT(*) as total_orders
                ')
                ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
                ->groupBy('month_key', 'month_label')
                ->orderBy('month_key', 'asc')
                ->get();
        }

        $salesMonths = $monthlySales->pluck('month_label')->toArray();
        $salesRevenueTrend = $monthlySales->pluck('total_sales')->map(fn($v) => round((float)$v, 2))->toArray();
        $salesOrderCountTrend = $monthlySales->pluck('total_orders')->map(fn($v) => (int)$v)->toArray();

        // High-level analytics highlights for executive cockpit
        $peakMonthObj = $monthlySales->sortByDesc('total_sales')->first();
        $peakMonthLabel = $peakMonthObj ? $peakMonthObj->month_label : 'N/A';
        $peakMonthSales = $peakMonthObj ? (float)$peakMonthObj->total_sales : 0.0;
        $avgMonthlySales = $monthlySales->isNotEmpty() ? ($monthlySales->sum('total_sales') / $monthlySales->count()) : 0.0;

        // =========================================================================
        // 6. 🍩 SALES BY PRODUCT CATEGORY BREAKDOWN (Donut Chart & Distribution)
        // =========================================================================
        $catOrderQuery = Order::whereIn('status', ['completed', 'approved']);
        if ($startDate) {
            $catOrderQuery->where('placed_at', '>=', $startDate);
        }
        if ($selectedOutletId && $outletUserIds && $outletUserIds->isNotEmpty()) {
            $catOrderQuery->whereIn('user_id', $outletUserIds);
        }
        $catOrderIds = $catOrderQuery->pluck('id');
        if ($catOrderIds->isEmpty()) {
            $catOrderIds = Order::whereIn('status', ['completed', 'approved'])->pluck('id');
        }

        $categorySales = OrderItem::whereIn('order_items.order_id', $catOrderIds)
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->selectRaw('COALESCE(categories.name, order_items.category_name, "Uncategorized") as category, SUM(order_items.line_total) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $categoryColors = ['#4f46e5', '#06b6d4', '#f59e0b', '#10b981', '#ec4899'];
        $totalCatSales = $categorySales->sum('total') ?: 1;
        foreach ($categorySales as $idx => $cs) {
            $cs->percentage = round(($cs->total / $totalCatSales) * 100, 1);
            $cs->color = $categoryColors[$idx % count($categoryColors)];
        }

        $categoryLabels = $categorySales->pluck('category')->toArray();
        $categoryTotals = $categorySales->pluck('total')->map(fn($v) => round((float)$v, 2))->toArray();

        // Legacy chart compatibility
        $issueLabels = collect($salesMonths);
        $issueData = collect($salesRevenueTrend);
        $statusData = [
            Order::where('status', 'pending')->count(),
            Order::whereIn('status', ['approved', 'completed'])->count(),
            Order::whereIn('status', ['cancelled', 'rejected'])->count()
        ];

        // =========================================================================
        // 7. 📋 RECENT COMMERCIAL ORDERS STREAM
        // =========================================================================
        $recentOrders = Order::with('user:id,name,outlet_name')
            ->orderByDesc('id')
            ->take(6)
            ->get(['id', 'order_no', 'user_id', 'total_amount', 'due_amount', 'status', 'created_at']);
        $recentRequests = $recentOrders;

        // Outlet user fallback stats
        $myTotalRequests = 0;
        $myPendingRequests = 0;
        $myTotalSpent = 0;
        if (!$user->can('Manage Reports')) {
            $myTotalRequests = Order::where('user_id', $user->id)->count();
            $myPendingRequests = Order::where('user_id', $user->id)->where('status', 'pending')->count();
            $myTotalSpent = Order::where('user_id', $user->id)->whereIn('status', ['approved', 'completed'])->sum('total_amount');
        }

        return view('backend.dashboard', compact(
            'totalRevenue',
            'cogs',
            'accountsReceivable',
            'accountsPayable',
            'inventoryValuation',
            'grossProfit',
            'grossMarginPct',
            'selectedPeriod',
            'ordersInPipeline',
            'pendingApprovals',
            'lowStockCount',
            'activeOutlets',
            'inTransitTransfers',
            'totalCatalogProducts',
            'totalProducts',
            'totalActiveProducts',
            'totalInactiveProducts',
            'totalInventoryUnits',
            'totalPurchaseOrders',
            'activeLcs',
            'totalGrns',
            'totalPurchaseValue',
            'totalDeliveryOrders',
            'totalSalesInvoices',
            'totalIssues',
            'pendingRequests',
            'myTotalRequests',
            'myPendingRequests',
            'myTotalSpent',
            'bestSellerProducts',
            'topCustomers',
            'salesMonths',
            'salesRevenueTrend',
            'salesOrderCountTrend',
            'peakMonthLabel',
            'peakMonthSales',
            'avgMonthlySales',
            'categorySales',
            'totalCatSales',
            'categoryLabels',
            'categoryTotals',
            'issueLabels',
            'issueData',
            'statusData',
            'recentOrders',
            'recentRequests',
            'outlets',
            'selectedOutletId'
        ));
    }
}
