<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $sellerId = Auth::id();
        
        // Get seller's orders and products
        $orders = Order::where('seller_id', $sellerId)->get();
        $products = Product::where('seller_id', $sellerId)->get();
        
        // Calculate statistics
        $totalOrders = $orders->count();
        $totalProducts = $products->count();
        $totalRevenue = $orders->sum('total_amount');
        $pendingOrders = $orders->where('status', 'pending')->count();
        
        // Get today's revenue
        $todayRevenue = $orders->whereDate('created_at', today())->sum('total_amount');
        
        // Get orders by status
        $ordersByStatus = $orders->groupBy('status')->map(function ($group) {
            return [
                'status' => $group->first()->status,
                'count' => $group->count(),
            ];
        })->values()->toArray();
        
        // Get top products
        $topProducts = $products->take(5)->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->title,
                'total_sold' => $product->orderItems()->count(),
                'total_revenue' => $product->orderItems()->sum('total_price'),
            ];
        })->toArray();
        
        // Get recent orders
        $recentOrders = $orders->sortByDesc('created_at')->take(5)->map(function ($order) {
            return [
                'id' => $order->id,
                'total_amount' => (float) $order->total_amount,
                'status' => $order->status,
                'created_at' => $order->created_at->toIso8601String(),
                'order_items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product' => [
                            'name' => $item->product->title ?? 'Product',
                        ],
                        'quantity' => $item->quantity,
                        'price' => (float) $item->price,
                    ];
                })->toArray(),
            ];
        })->values()->toArray();
        
        // Get monthly revenue
        $monthlyRevenue = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $revenue = $orders
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('total_amount');
            
            $monthlyRevenue[] = [
                'month' => $date->month,
                'year' => $date->year,
                'revenue' => (float) $revenue,
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => [
                    'total_orders' => $totalOrders,
                    'total_products' => $totalProducts,
                    'total_revenue' => (float) $totalRevenue,
                    'pending_orders' => $pendingOrders,
                    'today_revenue' => (float) $todayRevenue,
                ],
                'orders_by_status' => $ordersByStatus,
                'top_products' => $topProducts,
                'recent_orders' => $recentOrders,
                'monthly_revenue' => $monthlyRevenue,
            ]
        ]);
    }
}
