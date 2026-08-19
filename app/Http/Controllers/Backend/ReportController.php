<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\AuditLog;
use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\User;
use App\Models\Vendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ReportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:Manage Reports'),
        ];
    }

    /**
     * Reports Dashboard
     */
    public function index()
    {
        // 1. Total Stock Value: Using Weighted Average Cost from PurchaseDetails
        // We calculate the average purchase price per product and multiply by current stock
        $totalStockValue = DB::table('inventory_stocks')
            ->join('products', 'inventory_stocks.product_id', '=', 'products.id')
            ->join(DB::raw('(SELECT product_id, AVG(unit_cost) as avg_cost FROM purchase_details GROUP BY product_id) as costs'), 'products.id', '=', 'costs.product_id')
            ->where('products.status', 1)
            ->sum(DB::raw('inventory_stocks.quantity * costs.avg_cost'));
        
        $totalProducts = Product::where('status', 1)->count();
        
        $lowStockCount = Product::where('status', 1)
            ->withSum('inventoryStocks', 'quantity')
            ->havingRaw('inventory_stocks_sum_quantity <= 100 OR inventory_stocks_sum_quantity IS NULL')
            ->get()
            ->count();
        
        // 2. Total Revenue: From Posted Sales Invoices (or Approved/Completed Orders)
        $invoiceRevenue = (float) \App\Models\SalesInvoice::whereIn('status', ['posted', 'paid'])->sum('total_amount');
        if ($invoiceRevenue > 0) {
            $totalRevenue = $invoiceRevenue;
        } else {
            $totalRevenue = (float) Order::whereIn('status', ['approved', 'processing', 'completed'])->sum('total_amount');
        }
        
        // 3. COGS (Cost of Goods Sold): Based on actual commercial order quantities * average purchase cost
        $totalCost = (float) DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.status', ['approved', 'processing', 'completed'])
            ->leftJoin(DB::raw('(SELECT product_id, AVG(unit_cost) as avg_cost FROM purchase_details GROUP BY product_id) as costs'), 'order_items.product_id', '=', 'costs.product_id')
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->sum(DB::raw('order_items.quantity * COALESCE(NULLIF(costs.avg_cost, 0), products.purchase_price, 0)'));

        $grossProfit = $totalRevenue - $totalCost;

        // Current Month Purchases for Context
        $monthlyPurchases = Purchase::whereMonth('date', date('m'))
            ->whereYear('date', date('Y'))
            ->sum('total_amount');

        return view('backend.reports.index', compact(
            'totalStockValue',
            'totalProducts',
            'lowStockCount',
            'monthlyPurchases',
            'totalRevenue',
            'grossProfit'
        ));
    }

    /**
     * Best Seller Products — Full paginated list
     * Matches the same logic as productFrequency in orderReport() — completed orders only.
     * Supports Year & Month filtering.
     */
    public function bestSellers(Request $request)
    {
        $orderQuery = Order::where('status', 'completed');
        if ($request->filled('year')) {
            $orderQuery->whereYear('placed_at', $request->year);
        }
        if ($request->filled('month')) {
            $orderQuery->whereMonth('placed_at', $request->month);
        }
        $completedOrderIds = $orderQuery->pluck('id');

        $query = OrderItem::whereIn('order_id', $completedOrderIds)
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->selectRaw('
                order_items.product_id,
                order_items.product_name,
                COUNT(*) as times_ordered,
                SUM(order_items.quantity) as total_qty,
                COALESCE(SUM(order_items.line_total), 0) as total_value
            ')
            ->groupBy('order_items.product_id', 'order_items.product_name');

        if ($request->filled('search')) {
            $query->where('order_items.product_name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category_id')) {
            $query->where('products.category_id', $request->category_id);
        }
        if ($request->filled('sub_category_id')) {
            $query->where('products.sub_category_id', $request->sub_category_id);
        }
        if ($request->filled('child_category_id')) {
            $query->where('products.child_category_id', $request->child_category_id);
        }

        $products = $query->orderByDesc('times_ordered')->paginate(30)->withQueryString();

        $grandTotals = OrderItem::whereIn('order_id', $completedOrderIds)
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->selectRaw('
                SUM(order_items.quantity) as grand_total_qty,
                COALESCE(SUM(order_items.line_total), 0) as grand_total_value
            ')
            ->when($request->filled('search'), fn($q) => $q->where('order_items.product_name', 'like', '%' . $request->search . '%'))
            ->when($request->filled('category_id'), fn($q) => $q->where('products.category_id', $request->category_id))
            ->when($request->filled('sub_category_id'), fn($q) => $q->where('products.sub_category_id', $request->sub_category_id))
            ->when($request->filled('child_category_id'), fn($q) => $q->where('products.child_category_id', $request->child_category_id))
            ->first();

        $availableYears = Order::where('status', 'completed')
            ->selectRaw('YEAR(placed_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $categories = Category::where('status', 1)->get();
        $settings = GeneralSetting::first();

        if ($request->ajax()) {
            $html = view('backend.reports.partials.best_sellers_table', compact('products', 'grandTotals', 'settings'))->render();
            return response()->json([
                'html' => $html,
                'pagination' => $products->links()->render(),
                'grand_total_qty' => number_format($grandTotals->grand_total_qty ?? 0),
                'grand_total_value' => formatWithCurrency($grandTotals->grand_total_value ?? 0),
                'total_products' => number_format($products->total()),
                'first_item' => $products->firstItem(),
                'last_item' => $products->lastItem(),
                'total' => $products->total(),
                'has_more' => $products->hasMorePages(),
            ]);
        }

        return view('backend.reports.best_sellers', compact('products', 'grandTotals', 'categories', 'settings', 'availableYears'));
    }

    /**
     * Top Customers — Full paginated list ordered by total value
     * Supports Year/Month filter + Monthly Trend tab
     */
    public function topCustomers(Request $request)
    {
        $query = Order::where('status', 'completed')
            ->has('user')
            ->selectRaw('
                user_id,
                COUNT(*) as total_orders,
                COALESCE(SUM(total_amount), 0) as total_value
            ')
            ->with('user:id,name,outlet_name,email')
            ->groupBy('user_id');

        if ($request->filled('search')) {
            $query->whereHas('user', fn($q) => $q
                ->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('outlet_name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%')
            );
        }

        // Year / Month filter for All Customers tab
        if ($request->filled('year')) {
            $query->whereYear('placed_at', $request->year);
        }
        if ($request->filled('month')) {
            $query->whereMonth('placed_at', $request->month);
        }

        // Grand totals (respects year/month filter)
        $grandQuery = Order::where('status', 'completed')->has('user');
        if ($request->filled('year'))  $grandQuery->whereYear('placed_at', $request->year);
        if ($request->filled('month')) $grandQuery->whereMonth('placed_at', $request->month);

        $grandTotals = $grandQuery->selectRaw('
            COUNT(DISTINCT user_id) as total_customers,
            COUNT(*) as grand_total_orders,
            COALESCE(SUM(total_amount), 0) as grand_total_value
        ')->first();

        $customers = $query->orderByDesc('total_value')->paginate(30)->withQueryString();

        // Available years for filter dropdown
        $availableYears = Order::where('status', 'completed')
            ->selectRaw('YEAR(placed_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $settings = GeneralSetting::first();

        // Monthly Trend — all customers grouped by month (skipped for AJAX)
        $monthlyTrend = collect(); // commented out for now — activate when needed
        // $monthlyTrend = Order::where('status', 'completed')
        //     ->has('user')
        //     ->selectRaw("DATE_FORMAT(placed_at, '%Y-%m') as month, user_id, COUNT(*) as total_orders, COALESCE(SUM(total_amount), 0) as total_value")
        //     ->with('user:id,name,outlet_name,email')
        //     ->groupBy('month', 'user_id')
        //     ->orderBy('month', 'desc')->orderByDesc('total_value')
        //     ->get()->groupBy('month');

        if ($request->ajax()) {
            $tableHtml = view('backend.reports.partials.top_customers_table',
                compact('customers', 'settings')
            )->render();

            $paginationHtml = $customers->links()->render();

            return response()->json([
                'table'            => $tableHtml,
                'pagination'       => (string) $paginationHtml,
                'total_customers'  => number_format($grandTotals->total_customers ?? 0),
                'total_orders'     => number_format($grandTotals->grand_total_orders ?? 0),
                'total_value'      => formatWithCurrency($grandTotals->grand_total_value ?? 0),
                'showing_from'     => $customers->firstItem() ?? 0,
                'showing_to'       => $customers->lastItem() ?? 0,
                'showing_total'    => $customers->total(),
            ]);
        }

        return view('backend.reports.top_customers', compact(
            'customers', 'grandTotals', 'settings',
            'monthlyTrend', 'availableYears'
        ));
    }

    /**
     * Stock Valuation Report
     */
    public function stockReport(Request $request)
    {
        $query = Product::with(['category', 'unit', 'brand', 'inventoryStocks'])
            ->withSum('inventoryStocks', 'quantity')
            ->where('status', 1);

        // Filters
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->brand_id) {
            $query->where('brand_id', $request->brand_id);
        }

        // Calculate Summary Stats from database aggregates BEFORE pagination
        $summaryQuery = DB::table('products')
            ->join('inventory_stocks', 'products.id', '=', 'inventory_stocks.product_id')
            ->join(DB::raw('(SELECT product_id, AVG(unit_cost) as avg_cost FROM purchase_details GROUP BY product_id) as costs'), 'products.id', '=', 'costs.product_id')
            ->where('products.status', 1);

        if ($request->category_id) {
            $summaryQuery->where('products.category_id', $request->category_id);
        }
        if ($request->brand_id) {
            $summaryQuery->where('products.brand_id', $request->brand_id);
        }

        $summaryData = $summaryQuery->selectRaw('
            SUM(inventory_stocks.quantity) as untyped_total_qty,
            SUM(inventory_stocks.quantity * costs.avg_cost) as untyped_total_value,
            SUM(inventory_stocks.quantity * products.price) as untyped_potential_revenue
        ')->first();

        $totalQty = $summaryData->untyped_total_qty ?? 0;
        $totalValue = $summaryData->untyped_total_value ?? 0;
        $potentialRevenue = $summaryData->untyped_potential_revenue ?? 0;
        $potentialProfit = $potentialRevenue - $totalValue;

        $products = $query->paginate(30)->withQueryString();
        
        $categories = Category::where('status', 1)->get();
        $brands = Brand::where('status', 1)->get();
        $settings = GeneralSetting::first();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backend.reports.partials.stock_table_rows', compact('products', 'settings'))->render(),
                'pagination' => $products->links()->render(),
                'totalQty' => number_format($totalQty),
                'totalValue' => $settings->currency_icon . number_format($totalValue, 2),
                'potentialRevenue' => $settings->currency_icon . number_format($potentialRevenue, 2),
                'potentialProfit' => $settings->currency_icon . number_format($potentialProfit, 2),
            ]);
        }

        return view('backend.reports.stock', compact('products', 'categories', 'brands', 'totalQty', 'totalValue', 'potentialRevenue', 'potentialProfit', 'settings'));
    }

    /**
     * Current Stock Report (Unified)
     */
    public function currentStockReport(Request $request)
    {
        $query = Product::with(['category', 'vendor', 'inventoryStocks'])
            ->withSum('inventoryStocks', 'quantity')
            ->where('status', 1);

        // Filter by Category
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by Stock Status
        if ($request->stock_status) {
            if ($request->stock_status == 'in_stock') {
                $query->having('inventory_stocks_sum_quantity', '>', 0);
            } elseif ($request->stock_status == 'out_of_stock') {
                $query->havingRaw('inventory_stocks_sum_quantity <= 0 OR inventory_stocks_sum_quantity IS NULL');
            }
        }

        // Filter by Vendor
        if ($request->vendor_id) {
            $query->where('vendor_id', $request->vendor_id);
        }

        // Vendor-wala products first (NULL vendor_id LAST), then latest product id
        $products = $query->orderByRaw('CASE WHEN vendor_id IS NULL THEN 1 ELSE 0 END ASC')
                          ->orderBy('vendor_id', 'asc')
                          ->orderBy('id', 'desc')
                          ->paginate(30)->withQueryString();
        $categories = Category::where('status', 1)->orderBy('id', 'desc')->get();
        $vendors = Vendor::where('status', 1)->orderBy('id', 'desc')->get();
        $settings = GeneralSetting::first();

        return view('backend.reports.current_stock', compact('products', 'categories', 'vendors', 'settings'));
    }

    /**
     * Export Current Stock Report
     */
    public function exportCurrentStockReport(Request $request)
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\CurrentStockExport($request->category_id, $request->stock_status, $request->vendor_id), 'current-stock-report-' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * Purchase History Report
     */
    public function purchaseReport(Request $request)
    {
        $query = Purchase::with(['vendor', 'user', 'details']);

        // Date Range Filter
        if ($request->start_date) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->where('date', '<=', $request->end_date);
        }

        // Vendor Filter
        if ($request->vendor_id) {
            $query->where('vendor_id', $request->vendor_id);
        }

        $purchases = $query->orderBy('date', 'desc')->paginate(30)->withQueryString();
        $vendors = Vendor::where('status', 1)->get();

        return view('backend.reports.purchase', compact('purchases', 'vendors'));
    }

    /**
     * Product-wise Purchase History (Track same product from different vendors)
     */
    public function productPurchaseHistory(Request $request)
    {
        $query = PurchaseDetail::with(['product', 'purchase.vendor', 'purchase.user']);

        // Product Filter
        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        $details = $query->orderBy('id', 'desc')->paginate(30)->withQueryString();
        $products = Product::where('status', 1)->get();

        return view('backend.reports.product_purchase_history', compact('details', 'products'));
    }

    /**
     * Low Stock Alert Report
     */
    public function lowStockReport(Request $request)
    {
        $query = Product::with(['category', 'unit'])
            ->withSum('inventoryStocks', 'quantity')
            ->where('status', 1)
            ->havingRaw('inventory_stocks_sum_quantity <= 100 OR inventory_stocks_sum_quantity IS NULL');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('product_number', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($subQ) use ($search) {
                        $subQ->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Vendor filter
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        $products = $query->orderBy('inventory_stocks_sum_quantity', 'asc')
            ->paginate(30)->withQueryString();

        $vendors = Vendor::where('status', 1)->orderBy('shop_name')->get();

        return view('backend.reports.low_stock', compact('products', 'vendors'));
    }

    /**
     * AJAX Endpoint for Combined Alerts
     */
    public function lowStockCheck()
    {
        $data = $this->getNotificationData();
        return response()->json($data);
    }

    /**
     * View all notifications page
     */
    public function allNotifications()
    {
        $data = $this->getNotificationData();
        return view('backend.notifications.all', ['notifications' => $data['notifications']]);
    }

    /**
     * Mark all notifications as read
     */
    public function markNotificationsRead()
    {
        session(['notifications_read_at' => now()]);
        return response()->json(['status' => 'success']);
    }

    private function getNotificationData()
    {
        $notifications = [];
        $lastReadAt = session('notifications_read_at');
        
        // Expire "Mark as Read" after 10 minutes
        if ($lastReadAt && $lastReadAt->diffInMinutes(now()) >= 10) {
            session()->forget('notifications_read_at');
            $lastReadAt = null;
        }

        $unreadCount = 0;
        
        // 1. Fetch Low Stock Products (Threshold 100)
        $lowStockProducts = Product::where('status', 1)
            ->withSum('inventoryStocks', 'quantity')
            ->havingRaw('inventory_stocks_sum_quantity <= 100 OR inventory_stocks_sum_quantity IS NULL')
            ->orderBy('updated_at', 'desc') // Fetch by recent update
            ->take(15)
            ->get();

        foreach ($lowStockProducts as $product) {
            $isUnread = !$lastReadAt || $product->updated_at->gt($lastReadAt);
            if ($isUnread) $unreadCount++;

            $notifications[] = [
                'type' => 'lowStock',
                'title' => $product->name,
                'desc' => ($product->inventory_stocks_sum_quantity ?? 0) . ' in stock',
                'time' => $product->updated_at->diffForHumans(),
                'timestamp' => $product->updated_at->timestamp, // For sorting
                'url' => route('admin.reports.low-stock'),
                'icon' => 'fas fa-exclamation-triangle',
                'class' => ($product->inventory_stocks_sum_quantity <= 0) ? 'bg-danger' : 'bg-warning',
                'is_unread' => $isUnread,
                'is_out_of_stock' => ($product->inventory_stocks_sum_quantity <= 0)
            ];
        }

        // 2. Fetch Pending Product Requests (Admin only)
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user && $user->can('Manage Product Requests')) {
            $pendingRequests = ProductRequest::with('user')
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->take(15)
                ->get();

            foreach ($pendingRequests as $req) {
                $isUnread = !$lastReadAt || $req->created_at->gt($lastReadAt);
                if ($isUnread) $unreadCount++;

                $userName = $req->user ? $req->user->name : 'Unknown User';
                $notifications[] = [
                    'type' => 'request',
                    'title' => 'New Request: ' . $req->request_no,
                    'desc' => 'From ' . $userName . ' (Qty: ' . $req->total_qty . ')',
                    'time' => $req->created_at->diffForHumans(),
                    'timestamp' => $req->created_at->timestamp, // For sorting
                    'url' => route('admin.product-requests.index'),
                    'icon' => 'fas fa-box-open',
                    'class' => 'bg-info',
                    'is_unread' => $isUnread,
                    'is_out_of_stock' => false
                ];
            }
        }

        // 3. Fetch Pending User Registrations (Admin only)
        if ($user && $user->can('Administration')) {
            $pendingUsers = User::where('status', 0)
                ->where('role_id', '!=', 1) // Exclude main admin if somehow status=0
                ->orderBy('created_at', 'desc')
                ->take(15)
                ->get();

            foreach ($pendingUsers as $pUser) {
                $isUnread = !$lastReadAt || $pUser->created_at->gt($lastReadAt);
                if ($isUnread) $unreadCount++;

                // Map role_id to name
                $roleName = match($pUser->role_id) {
                    1 => 'Admin',
                    2 => 'User',
                    3 => 'Outlet User',
                    default => 'Unknown Role (' . $pUser->role_id . ')'
                };

                $notifications[] = [
                    'type' => 'registration',
                    'title' => 'New User: ' . $pUser->name,
                    'desc' => 'Needs Approval (Role: ' . $roleName . ')',
                    'time' => $pUser->created_at->diffForHumans(),
                    'timestamp' => $pUser->created_at->timestamp,
                    'url' => route('admin.users.index'),
                    'icon' => 'fas fa-user-plus',
                    'class' => 'bg-primary',
                    'is_unread' => $isUnread,
                    'is_out_of_stock' => false
                ];
            }
        }

        // 4. Fetch PDF Ready Notifications from Cache
        if ($user) {
            $pdfCacheKey = 'user_pdf_notifications_' . $user->id;
            $pdfNotifications = \Illuminate\Support\Facades\Cache::get($pdfCacheKey, []);
            // \Illuminate\Support\Facades\Log::info("Fetching PDF notifications for user {$user->id} from key {$pdfCacheKey}. Found: " . count($pdfNotifications));
            
            foreach ($pdfNotifications as $pdfNotif) {
                $notifTime = \Illuminate\Support\Carbon::createFromTimestamp($pdfNotif['timestamp']);
                $isUnread = !$lastReadAt || $notifTime->gt($lastReadAt);
                if ($isUnread) $unreadCount++;

                $notifications[] = [
                    'type' => 'pdf_ready',
                    'title' => $pdfNotif['title'],
                    'desc' => $pdfNotif['desc'],
                    'time' => $notifTime->diffForHumans(),
                    'timestamp' => $pdfNotif['timestamp'],
                    'url' => $pdfNotif['url'],
                    'icon' => $pdfNotif['icon'],
                    'class' => $pdfNotif['class'],
                    'is_unread' => $isUnread,
                    'is_out_of_stock' => false
                ];
            }
        }
        usort($notifications, function($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        return [
            'count' => $unreadCount,
            'notifications' => array_slice($notifications, 0, 20) // Limit to top 20
        ];
    }

    /**
     * Profit & Loss Report
     */
    public function profitLossReport(Request $request)
    {
        $purchasesQuery = Purchase::query();
        if ($request->start_date) $purchasesQuery->where('date', '>=', $request->start_date);
        if ($request->end_date) $purchasesQuery->where('date', '<=', $request->end_date);

        $hasDateFilter = $request->start_date || $request->end_date;

        if ($hasDateFilter) {
            // Filtered: use order totals
            $orderQuery = Order::where('status', 'completed');
            if ($request->start_date) $orderQuery->whereDate('placed_at', '>=', $request->start_date);
            if ($request->end_date) $orderQuery->whereDate('placed_at', '<=', $request->end_date);

            $totalRevenue = (clone $orderQuery)->sum('total_amount');

            $totalCost = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join(DB::raw('(SELECT product_id, AVG(unit_cost) as avg_cost FROM purchase_details GROUP BY product_id) as costs'), 'order_items.product_id', '=', 'costs.product_id')
                ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
                ->where('orders.status', 'completed')
                ->when($request->start_date, fn($q) => $q->whereDate('orders.placed_at', '>=', $request->start_date))
                ->when($request->end_date, fn($q) => $q->whereDate('orders.placed_at', '<=', $request->end_date))
                ->sum(DB::raw('order_items.quantity * COALESCE(NULLIF(costs.avg_cost, 0), products.purchase_price, 0)'));
        } else {
            // Unfiltered: use issue items (P&L match, 10.4M all-time)
            $totalRevenue = DB::table('issue_items')
                ->sum(DB::raw('issue_items.quantity * COALESCE(issue_items.unit_price, 0)'));

            $totalCost = DB::table('issue_items')
                ->join(DB::raw('(SELECT product_id, AVG(unit_cost) as avg_cost FROM purchase_details GROUP BY product_id) as costs'), 'issue_items.product_id', '=', 'costs.product_id')
                ->leftJoin('products', 'issue_items.product_id', '=', 'products.id')
                ->sum(DB::raw('issue_items.quantity * COALESCE(NULLIF(costs.avg_cost, 0), products.purchase_price, 0)'));
        }

        // Calculate Profit
        $grossProfit = $totalRevenue - $totalCost;
        $profitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;
        
        $totalPurchases = $purchasesQuery->sum('total_amount');

        return view('backend.reports.profit_loss', compact(
            'totalRevenue',
            'totalCost',
            'grossProfit',
            'profitMargin',
            'totalPurchases'
        ));
    }

    /**
     * Order & Issue Report — two modes:
     *   Global  (user_id empty) → aggregate across all users
     *   360°    (user_id set)   → deep-dive for one user
     */
    public function orderReport(Request $request)
    {
        $users = User::role(['Outlet User', 'User'])->get(['id', 'name', 'outlet_name']);

        $query = Order::where('status', 'completed');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('placed_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('placed_at', '<=', $request->date_to);
        }
        if ($request->filled('month')) {
            $query->whereMonth('placed_at', $request->month);
        }
        if ($request->filled('year')) {
            $query->whereYear('placed_at', $request->year);
        }

        $orderIds = (clone $query)->pluck('id');

        $summary = (clone $query)->selectRaw('
            COUNT(*) as total_orders,
            COALESCE(SUM(total_amount),0) as total_value,
            COALESCE(AVG(total_amount),0) as avg_order_value
        ')->first();

        $totalRevenue = $summary->total_value;
        $issueStats = (object)['total_issues' => 0, 'total_issued_qty' => 0];

        if ($request->filled('user_id')) {
            $user = User::findOrFail($request->user_id);

            $paymentStats = OrderPayment::whereIn('order_id', $orderIds)
                ->selectRaw('COALESCE(SUM(amount),0) as total_paid')->first();
            $totalDue = $summary->total_value - $paymentStats->total_paid;

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
                ->where('user_id', $request->user_id)
                ->when($request->filled('date_from'), fn($q) => $q->whereDate('placed_at', '>=', $request->date_from))
                ->when($request->filled('date_to'), fn($q) => $q->whereDate('placed_at', '<=', $request->date_to))
                ->when($request->filled('month'), fn($q) => $q->whereMonth('placed_at', $request->month))
                ->when($request->filled('year'), fn($q) => $q->whereYear('placed_at', $request->year))
                ->selectRaw("DATE_FORMAT(placed_at, '%Y-%m') as month, COUNT(*) as orders_count, COALESCE(SUM(total_amount),0) as total_amount")
                ->groupBy('month')->orderBy('month', 'desc')
                ->get();

            return view('backend.reports.orders', compact(
                'user', 'users', 'summary', 'issueStats', 'orderIds',
                'paymentStats', 'totalDue', 'issueValue', 'pendingValue',
                'orders', 'issues', 'payments', 'productComparison', 'monthlyTrend', 'totalRevenue'
            ));
        }

        $issueValue = $totalRevenue;

        $orders = $query->with(['user', 'items.product'])->orderByDesc('placed_at')->paginate(30)->withQueryString();

        $productFrequency = OrderItem::whereIn('order_id', $orderIds)
            ->selectRaw('product_id, product_name, COUNT(*) as times_ordered, SUM(quantity) as total_qty, COALESCE(SUM(line_total),0) as total_value')
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('times_ordered')
            ->paginate(30)->withQueryString();

        $monthlyTrend = Order::where('status', 'completed')
            ->when($request->filled('user_id'), fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('placed_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('placed_at', '<=', $request->date_to))
            ->when($request->filled('month'), fn($q) => $q->whereMonth('placed_at', $request->month))
            ->when($request->filled('year'), fn($q) => $q->whereYear('placed_at', $request->year))
            ->selectRaw("DATE_FORMAT(placed_at, '%Y-%m') as month, COUNT(*) as orders_count, COALESCE(SUM(total_amount),0) as total_amount")
            ->groupBy('month')->orderBy('month', 'desc')
            ->get();

        $userSummary = DB::table('orders')
            ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'completed')
            ->whereIn('orders.id', $orderIds)
            ->selectRaw('orders.user_id, COUNT(DISTINCT orders.id) as total_orders, COALESCE(SUM(orders.total_amount),0) as total_value, COALESCE(SUM(order_items.quantity),0) as total_qty')
            ->groupBy('orders.user_id')
            ->orderByDesc('total_value')
            ->get()
            ->keyBy('user_id');

        return view('backend.reports.orders', compact(
            'summary', 'issueStats', 'productFrequency', 'monthlyTrend', 'userSummary', 'users', 'orderIds', 'orders', 'issueValue', 'totalRevenue'
        ));
    }

    /**
     * Order & Issue Report — PDF Export
     */
    public function orderReportPdf(Request $request)
    {
        $query = Order::with(['user', 'items.product'])->where('status', 'completed');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('placed_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('placed_at', '<=', $request->date_to);
        }
        if ($request->filled('month')) {
            $query->whereMonth('placed_at', $request->month);
        }
        if ($request->filled('year')) {
            $query->whereYear('placed_at', $request->year);
        }

        $orderIds = (clone $query)->pluck('id');

        $summary = (clone $query)->selectRaw('
            COUNT(*) as total_orders,
            COALESCE(SUM(total_amount),0) as total_value,
            COALESCE(AVG(total_amount),0) as avg_order_value
        ')->first();

        $hasDateFilter = $request->filled('month') || $request->filled('year') || $request->filled('date_from') || $request->filled('date_to');

        $totalRevenue = $summary->total_value;
        $issueStats = (object)['total_issues' => 0, 'total_issued_qty' => 0];

        $settings = GeneralSetting::first();

        // ─── 360° per-user PDF ───────────────────────────────────
        if ($request->filled('user_id')) {
            $user = User::find($request->user_id);

            $paymentStats = OrderPayment::whereIn('order_id', $orderIds)
                ->selectRaw('COALESCE(SUM(amount),0) as total_paid')->first();
            $totalDue = $summary->total_value - $paymentStats->total_paid;

            $issueValue = 0;
            $pendingValue = 0;

            // Orders
            $orders = $query->with('items')->orderByDesc('placed_at')->get();
            $issues = collect();

            // Payments
            $payments = OrderPayment::with('order')
                ->whereIn('order_id', $orderIds)
                ->orderByDesc('created_at')
                ->get();

            // Product comparison
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

            // Monthly trend
            $monthlyTrend = Order::where('status', 'completed')
                ->where('user_id', $request->user_id)
                ->when($request->filled('date_from'), fn($q) => $q->whereDate('placed_at', '>=', $request->date_from))
                ->when($request->filled('date_to'), fn($q) => $q->whereDate('placed_at', '<=', $request->date_to))
                ->when($request->filled('month'), fn($q) => $q->whereMonth('placed_at', $request->month))
                ->when($request->filled('year'), fn($q) => $q->whereYear('placed_at', $request->year))
                ->selectRaw("DATE_FORMAT(placed_at, '%Y-%m') as month, COUNT(*) as orders_count, COALESCE(SUM(total_amount),0) as total_amount")
                ->groupBy('month')->orderBy('month', 'desc')
                ->get();

            $pdf = Pdf::loadView('backend.reports.orders_pdf', compact(
                'user', 'summary', 'issueStats', 'paymentStats', 'totalDue', 'issueValue', 'pendingValue',
                'orders', 'issues', 'payments', 'productComparison', 'monthlyTrend', 'settings', 'request', 'totalRevenue', 'orderIds'
            ))->setPaper('a4', 'landscape');
        } else {
            // ─── Global PDF ───────────────────────────────────────
            $monthlyTrend = Order::where('status', 'completed')
                ->when($request->filled('user_id'), fn($q) => $q->where('user_id', $request->user_id))
                ->when($request->filled('date_from'), fn($q) => $q->whereDate('placed_at', '>=', $request->date_from))
                ->when($request->filled('date_to'), fn($q) => $q->whereDate('placed_at', '<=', $request->date_to))
                ->when($request->filled('month'), fn($q) => $q->whereMonth('placed_at', $request->month))
                ->when($request->filled('year'), fn($q) => $q->whereYear('placed_at', $request->year))
                ->selectRaw("DATE_FORMAT(placed_at, '%Y-%m') as month, COUNT(*) as orders_count, COALESCE(SUM(total_amount),0) as total_amount")
                ->groupBy('month')->orderBy('month', 'desc')
                ->get();

            $issueValue = Issue::leftJoin('issue_items', 'issues.id', '=', 'issue_items.issue_id')
                ->where(function ($q) use ($request) {
                    if ($request->filled('date_from')) $q->whereDate('issues.created_at', '>=', $request->date_from);
                    if ($request->filled('date_to')) $q->whereDate('issues.created_at', '<=', $request->date_to);
                    if ($request->filled('month')) $q->whereMonth('issues.created_at', $request->month);
                    if ($request->filled('year')) $q->whereYear('issues.created_at', $request->year);
                })
                ->sum(DB::raw('issue_items.quantity * COALESCE(issue_items.unit_price, 0)'));

            $productFrequency = OrderItem::whereIn('order_id', $orderIds)
                ->selectRaw('product_id, product_name, COUNT(*) as times_ordered, SUM(quantity) as total_qty, COALESCE(SUM(line_total),0) as total_value')
                ->groupBy('product_id', 'product_name')
                ->orderByDesc('times_ordered')
                ->get();

            $userSummary = Issue::leftJoin('issue_items', 'issues.id', '=', 'issue_items.issue_id')
                ->where(function ($q) use ($request) {
                    if ($request->filled('date_from')) $q->whereDate('issues.created_at', '>=', $request->date_from);
                    if ($request->filled('date_to')) $q->whereDate('issues.created_at', '<=', $request->date_to);
                    if ($request->filled('month')) $q->whereMonth('issues.created_at', $request->month);
                    if ($request->filled('year')) $q->whereYear('issues.created_at', $request->year);
                })
                ->selectRaw('COALESCE(outlet_id, 0) as user_id, COUNT(DISTINCT issues.id) as total_orders, COALESCE(SUM(issue_items.quantity * COALESCE(issue_items.unit_price, 0)),0) as total_value, COALESCE(SUM(issue_items.quantity),0) as total_qty')
                ->groupBy('outlet_id')
                ->orderByDesc('total_value')
                ->get()
                ->keyBy('user_id');

            $pdf = Pdf::loadView('backend.reports.orders_pdf', compact(
                'summary', 'issueStats', 'productFrequency', 'monthlyTrend', 'userSummary', 'settings', 'request', 'orderIds', 'issueValue', 'totalRevenue'
            ))->setPaper('a4', 'landscape');
        }

        $fileName = 'order-issue-report-' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * Order & Issue Report — Async PDF Generation (Background Job)
     */
    public function orderReportPdfAsync(Request $request)
    {
        $filters = $request->only(['user_id', 'month', 'year', 'date_from', 'date_to']);

        dispatch(new \App\Jobs\GenerateReportPdfJob($filters, auth()->id()));

        Toastr::info('Order & Issue Report is generating in the background. Check notifications when ready.');

        return redirect()->back();
    }

    /**
     * Order & Issue Report — Download Generated PDF
     */
    public function downloadReportPdf($file)
    {
        $path = storage_path('app/public/reports/' . $file);

        if (!file_exists($path)) {
            return redirect()->back()->with('error', 'File not found or has expired.');
        }

        return response()->download($path)->deleteFileAfterSend(true);
    }

    /**
     * Audit trail report.
     */
    public function auditReport(Request $request)
    {
        $query = AuditLog::with(['user', 'vendor'])->latest();

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->user_id);
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', (int) $request->vendor_id);
        }

        if ($request->filled('reference')) {
            $reference = trim((string) $request->reference);
            $query->where('reference_no', 'like', '%' . $reference . '%');
        }

        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
        }

        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        $logs = $query->paginate(30)->withQueryString();
        $summaryQuery = clone $query;

        $summary = [
            'count' => (clone $summaryQuery)->count(),
            'today_count' => (clone $summaryQuery)->whereDate('created_at', today())->count(),
            'modules' => (clone $summaryQuery)->select('module')->distinct()->count('module'),
            'users' => (clone $summaryQuery)->whereNotNull('user_id')->distinct()->count('user_id'),
        ];

        $modules = AuditLog::query()
            ->whereNotNull('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        $actions = AuditLog::query()
            ->whereNotNull('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $users = User::query()->orderBy('name')->get(['id', 'name']);
        $vendors = Vendor::query()->orderBy('shop_name')->get(['id', 'shop_name']);

        return view('backend.reports.audit', compact('logs', 'summary', 'modules', 'actions', 'users', 'vendors'));
    }

}
