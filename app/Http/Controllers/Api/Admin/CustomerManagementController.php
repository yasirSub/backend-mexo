<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;

class CustomerManagementController extends Controller
{
    /**
     * Get all customers
     */
    public function index(Request $request)
    {
        $query = User::withCount('orders')
            ->withSum('orders as total_spent', 'total_amount');

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $perPage = $request->query('per_page', 15);
        $customers = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $customers->items(),
            'meta' => [
                'current_page' => $customers->currentPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
                'last_page' => $customers->lastPage(),
            ],
        ], 200);
    }

    /**
     * Get single customer details
     */
    public function show($id)
    {
        $customer = User::withCount('orders')
            ->withSum('orders as total_spent', 'total_amount')
            ->findOrFail($id);

        // Get recent orders
        $recentOrders = Order::where('user_id', $id)
            ->with(['seller', 'items.product'])
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'customer' => $customer,
                'recent_orders' => $recentOrders,
            ],
        ], 200);
    }

    /**
     * Block/Unblock customer
     */
    public function toggleStatus($id)
    {
        $customer = User::findOrFail($id);
        
        $customer->is_active = !$customer->is_active;
        $customer->save();

        return response()->json([
            'success' => true,
            'message' => $customer->is_active ? 'Customer activated' : 'Customer blocked',
            'data' => $customer,
        ], 200);
    }

    /**
     * Get customer statistics
     */
    public function statistics()
    {
        $totalCustomers = User::count();
        $activeCustomers = User::where('is_active', true)->count();
        $blockedCustomers = User::where('is_active', false)->count();
        
        $customersThisMonth = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $topCustomers = User::withSum('orders as total_spent', 'total_amount')
            ->withCount('orders')
            ->orderByDesc('total_spent')
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_customers' => $totalCustomers,
                'active_customers' => $activeCustomers,
                'blocked_customers' => $blockedCustomers,
                'customers_this_month' => $customersThisMonth,
                'top_customers' => $topCustomers,
            ],
        ], 200);
    }
}
