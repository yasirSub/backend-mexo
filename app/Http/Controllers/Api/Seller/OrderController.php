<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $seller = $request->user();
        $status = $request->query('status'); // all, pending, shipped, delivered

        $query = Order::where('seller_id', $seller->id)
            ->with(['items.product', 'user']);

        // Filter by status if provided
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'customer_name' => $order->user->name ?? 'Guest Customer',
                    'customer_email' => $order->user->email ?? 'N/A',
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'items_count' => $order->items->count(),
                    'items' => $order->items->map(function ($item) {
                        return [
                            'product_name' => $item->product->title ?? 'Unknown Product',
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                        ];
                    }),
                    'shipping_address' => is_array($order->shipping_address) 
                        ? implode(', ', array_filter($order->shipping_address))
                        : $order->shipping_address,
                    'created_at' => $order->created_at->toISOString(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    public function show(Request $request, $id)
    {
        $seller = $request->user();
        
        $order = Order::where('seller_id', $seller->id)
            ->where('id', $id)
            ->with(['items.product', 'user'])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->user->name ?? 'Guest Customer',
                'customer_email' => $order->user->email ?? 'N/A',
                'customer_phone' => $order->user->phone ?? 'N/A',
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'items' => $order->items->map(function ($item) {
                    return [
                        'product_name' => $item->product->title ?? 'Unknown Product',
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'subtotal' => $item->quantity * $item->price,
                    ];
                }),
                'shipping_address' => is_array($order->shipping_address) 
                    ? implode(', ', array_filter($order->shipping_address))
                    : $order->shipping_address,
                'created_at' => $order->created_at->toISOString(),
                'updated_at' => $order->updated_at->toISOString(),
            ],
        ]);
    }

    public function ship(Request $request, $id)
    {
        $seller = $request->user();
        
        $validated = $request->validate([
            'tracking_number' => 'nullable|string',
            'courier' => 'nullable|string',
        ]);

        $order = Order::where('seller_id', $seller->id)
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending orders can be shipped',
            ], 400);
        }

        $order->status = 'shipped';
        
        if (isset($validated['tracking_number'])) {
            $order->notes = ($order->notes ?? '') . "\nTracking: " . $validated['tracking_number'];
        }
        if (isset($validated['courier'])) {
            $order->notes = ($order->notes ?? '') . "\nCourier: " . $validated['courier'];
        }
        
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Order marked as shipped',
            'data' => [
                'id' => $order->id,
                'status' => $order->status,
                'tracking_number' => $validated['tracking_number'] ?? null,
                'shipped_at' => now()->toISOString(),
            ],
        ]);
    }

    public function deliver(Request $request, $id)
    {
        $seller = $request->user();
        
        $order = Order::where('seller_id', $seller->id)
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        if ($order->status !== 'shipped') {
            return response()->json([
                'success' => false,
                'message' => 'Only shipped orders can be marked as delivered',
            ], 400);
        }

        $order->status = 'delivered';
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Order marked as delivered',
            'data' => [
                'id' => $order->id,
                'status' => $order->status,
                'delivered_at' => now()->toISOString(),
            ],
        ]);
    }
}
