<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Initialize all variables with default values to prevent "Undefined variable" errors in view
        $totalActiveProducts = $totalInactiveProducts = $totalProducts = $totalIssues = $pendingRequests = $totalOutlets = 0;
        $totalPurchaseOrders = $activeLcs = $totalGrns = $totalPurchaseValue = 0;
        $myTotalRequests = $myPendingRequests = 0;
        $myTotalSpent = 0;
        $recentRequests = collect();
        $issueLabels = $issueData = $statusData = collect();
        $bestSellerProducts = collect();
        $topCustomers = collect();

        if ($user->can('Manage Reports')) {
            // Admin Global Stats
            $totalActiveProducts = Product::where('status', 1)->count();
            $totalInactiveProducts = Product::where('status', 0)->count();
            $totalProducts = Product::count();
            $totalIssues = \App\Models\DeliveryOrder::count();
            $pendingRequests = Order::where('status', 'pending')->count();
            $totalOutlets = User::role('Outlet User')->count();

            // Phase 2 Procurement Stats
            $totalPurchaseOrders = \App\Models\Purchase::count();
            $activeLcs = \App\Models\LetterOfCredit::count();
            $totalGrns = \App\Models\GoodsReceipt::count();
            $totalPurchaseValue = \App\Models\Purchase::where('status', 1)->sum('total_amount');
            
            // Recent frontend orders for Admin
            $recentRequests = Order::with('user')->orderByDesc('id')->take(5)->get();

            // Chart Data: Monthly Deliveries (Last 12 Months)
            $monthlyIssues = \App\Models\DeliveryOrder::select(
                DB::raw('count(*) as total'),
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month_year"),
                DB::raw("DATE_FORMAT(created_at, '%M') as month_name")
            )
            ->where('created_at', '>=', now()->subMonths(11))
            ->groupBy('month_year', 'month_name')
            ->orderBy('month_year')
            ->get();

            $issueLabels = $monthlyIssues->pluck('month_name');
            $issueData = $monthlyIssues->pluck('total');

            // Chart Data: Request Status Distribution
            $requestStatus = Order::select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status');
            
            // Ensure all statuses are present for consistent coloring
            $statuses = ['pending', 'approved', 'rejected'];
            $statusData = [];
            foreach ($statuses as $status) {
                $statusData[] = $requestStatus[$status] ?? 0;
            }

            // Best Seller Products top 5 — completed orders only (matches orderReport productFrequency logic)
            $completedOrderIds = Order::where('status', 'completed')->pluck('id');
            $bestSellerProducts = OrderItem::whereIn('order_id', $completedOrderIds)
                ->selectRaw('
                    product_id,
                    product_name,
                    COUNT(*) as times_ordered,
                    SUM(quantity) as total_qty
                ')
                ->groupBy('product_id', 'product_name')
                ->orderByDesc('times_ordered')
                ->take(5)
                ->get();

            // Top Customers (top 5 by total value)
            $topCustomers = Order::where('status', 'completed')
                ->has('user')
                ->selectRaw('
                    user_id,
                    COUNT(*) as total_orders,
                    COALESCE(SUM(total_amount), 0) as total_value
                ')
                ->with('user:id,name,outlet_name')
                ->groupBy('user_id')
                ->orderByDesc('total_value')
                ->take(5)
                ->get();
        } 
        
        // Calculate Outlet-specific stats for any user who is NOT a report manager (non-admin)
        if (!$user->can('Manage Reports')) {
            $myTotalRequests = Order::where('user_id', $user->id)->count();
            $myPendingRequests = Order::where('user_id', $user->id)->where('status', 'pending')->count();
            $myTotalSpent = Order::where('user_id', $user->id)
                ->whereIn('status', ['approved', 'completed', 'complete']) 
                ->sum('total_amount');
            
            // Only override recentRequests if not already set by Admin logic
            if ($recentRequests->isEmpty()) {
                $recentRequests = Order::where('user_id', $user->id)->with('user')->orderByDesc('id')->take(5)->get();
            }
        }

        // Phase 3 Sales & Fulfillment Stats
        $totalDeliveryOrders = \App\Models\DeliveryOrder::count();
        $totalSalesInvoices = \App\Models\SalesInvoice::count();

        return view('backend.dashboard', compact(
            'totalActiveProducts',
            'totalInactiveProducts',
            'totalProducts',
            'totalIssues',
            'pendingRequests',
            'totalOutlets',
            'myTotalRequests',
            'myPendingRequests',
            'myTotalSpent',
            'recentRequests',
            'issueLabels',
            'issueData',
            'statusData',
            'bestSellerProducts',
            'topCustomers',
            'totalPurchaseOrders',
            'activeLcs',
            'totalGrns',
            'totalPurchaseValue',
            'totalDeliveryOrders',
            'totalSalesInvoices'
        ));
    }
}
