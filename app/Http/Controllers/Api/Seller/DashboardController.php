<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $seller = $request->user();

        // Get real stats from database
        $totalOrders = Order::where('seller_id', $seller->id)->count();
        $revenue = Order::where('seller_id', $seller->id)
            ->whereIn('status', ['shipped', 'delivered'])
            ->sum('total_amount');
        $totalProducts = Product::where('seller_id', $seller->id)->count();
        $pendingOrders = Order::where('seller_id', $seller->id)
            ->where('status', 'pending')
            ->count();

        // Get recent orders
        $recentOrders = Order::where('seller_id', $seller->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'amount' => $order->total_amount,
                    'status' => $order->status,
                    'created_at' => $order->created_at->toISOString(),
                ];
            });

        $stats = [
            'total_orders' => $totalOrders,
            'revenue' => (float) $revenue,
            'products' => $totalProducts,
            'pending_orders' => $pendingOrders,
            'recent_orders' => $recentOrders,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'recent_orders' => $recentOrders,
            ],
        ]);
    }
}
