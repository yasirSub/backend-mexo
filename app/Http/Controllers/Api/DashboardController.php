<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function getStatistics(Request $request)
    {
        $seller = $request->user();
        
        // Debug: Log seller info
        Log::info('Dashboard API - Seller ID: ' . $seller->id . ', Seller Email: ' . $seller->email);
        
        $totalOrders = Order::where('seller_id', $seller->id)->count();
        $totalProducts = Product::where('seller_id', $seller->id)->count();
        $totalRevenue = Order::where('seller_id', $seller->id)
            ->whereRaw('LOWER(status) = ?', ['delivered'])
            ->sum('total_amount');
        
        // Debug: Log actual counts
        Log::info('Dashboard Stats - Orders: ' . $totalOrders . ', Products: ' . $totalProducts . ', Revenue: ' . $totalRevenue);
        
        $ordersByStatus = Order::where('seller_id', $seller->id)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        $topProducts = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.seller_id', $seller->id)
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->take(5)
            ->get();

        $recentOrders = Order::with(['items.product', 'user'])
            ->where('seller_id', $seller->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'total_amount' => (float) $order->total_amount,
                    'status' => $order->status,
                    'created_at' => $order->created_at->toISOString(),
                    'customer_name' => $order->user->name ?? 'Guest Customer',
                    'items_count' => $order->items->count(),
                    'order_items' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'quantity' => $item->quantity,
                            'price' => (float) $item->price,
                            'product' => [
                                'name' => $item->product->name ?? 'Unknown Product',
                            ],
                        ];
                    }),
                ];
            });

        $monthlyRevenue = Order::where('seller_id', $seller->id)
            ->whereRaw('LOWER(status) = ?', ['delivered'])
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(12)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => [
                    'total_orders' => $totalOrders,
                    'total_products' => $totalProducts,
                    'total_revenue' => round($totalRevenue, 2),
                ],
                'orders_by_status' => $ordersByStatus->map(function ($status) {
                    return [
                        'status' => $status->status,
                        'count' => $status->count,
                    ];
                }),
                'top_products' => $topProducts->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'total_sold' => (int) $product->total_sold,
                        'total_revenue' => (float) $product->total_revenue,
                    ];
                }),
                'recent_orders' => $recentOrders,
                'monthly_revenue' => $monthlyRevenue->map(function ($revenue) {
                    return [
                        'month' => (int) $revenue->month,
                        'year' => (int) $revenue->year,
                        'revenue' => (float) $revenue->revenue,
                    ];
                }),
            ],
        ]);
    }

    public function getProductAnalytics(Request $request, $productId)
    {
        $seller = $request->user();
        
        $product = Product::where('seller_id', $seller->id)
            ->findOrFail($productId);

        $salesData = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.seller_id', $seller->id)
            ->where('order_items.product_id', $productId)
            ->select(
                DB::raw('DATE(orders.created_at) as date'),
                DB::raw('SUM(order_items.quantity) as units_sold'),
                DB::raw('SUM(order_items.quantity * order_items.price) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->take(30)
            ->get();

        return response()->json([
            'product' => $product,
            'sales_data' => $salesData,
        ]);
    }

    public function getInventoryAlerts(Request $request)
    {
        $seller = $request->user();
        
        $lowStockProducts = Product::where('seller_id', $seller->id)
            ->where('stock', '<=', DB::raw('reorder_point'))
            ->select('id', 'name', 'stock', 'reorder_point')
            ->get();

        return response()->json([
            'low_stock_products' => $lowStockProducts
        ]);
    }
}