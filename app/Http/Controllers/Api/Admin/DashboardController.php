<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get Dashboard Statistics
     */
    public function stats(Request $request)
    {
        // Get seller statistics
        $totalSellers = Seller::count();
        $activeSellers = Seller::where('status', 'active')->count();
        $pendingSellers = Seller::where('status', 'pending')->count();

        // Get product statistics
        $totalProducts = Product::count();
        $activeProducts = Product::where('status', 'active')->count();

        // Get order statistics
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $completedOrders = Order::where('status', 'delivered')->count();

        // Get revenue statistics
        $totalRevenue = Order::whereIn('status', ['delivered', 'completed'])
            ->sum('total_amount');
        $pendingPayouts = Order::where('status', 'delivered')
            ->where('payout_status', 'pending')
            ->sum('total_amount');

        return response()->json([
            'success' => true,
            'data' => [
                'total_sellers' => $totalSellers,
                'active_sellers' => $activeSellers,
                'pending_sellers' => $pendingSellers,
                'total_products' => $totalProducts,
                'active_products' => $activeProducts,
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'completed_orders' => $completedOrders,
                'total_revenue' => $totalRevenue,
                'pending_payouts' => $pendingPayouts,
            ],
        ], 200);
    }

    /**
     * Get Sales Analytics
     */
    public function analytics(Request $request)
    {
        $period = $request->query('period', '7days');

        // Calculate date range based on period
        $startDate = match($period) {
            '7days' => now()->subDays(7),
            '30days' => now()->subDays(30),
            '6months' => now()->subMonths(6),
            '1year' => now()->subYear(),
            default => now()->subDays(7),
        };

        // Get revenue chart data
        $revenueChart = Order::where('created_at', '>=', $startDate)
            ->whereIn('status', ['delivered', 'completed'])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get order chart data
        $orderChart = Order::where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get top selling products
        $topProducts = Product::select('products.*', DB::raw('COUNT(order_items.id) as sales_count'))
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->groupBy('products.id')
            ->orderByDesc('sales_count')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'revenue_chart' => $revenueChart,
                'order_chart' => $orderChart,
                'top_products' => $topProducts,
            ],
        ], 200);
    }
}
