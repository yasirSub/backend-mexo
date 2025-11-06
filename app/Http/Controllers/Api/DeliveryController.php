<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\DeliveryTracking;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function updateTracking(Request $request, $orderId)
    {
        $request->validate([
            'status' => 'required|in:preparing,picked_up,in_transit,out_for_delivery,delivered',
            'location' => 'required|string',
            'description' => 'nullable|string'
        ]);

        $order = Order::findOrFail($orderId);
        
        $tracking = DeliveryTracking::create([
            'order_id' => $orderId,
            'status' => $request->status,
            'location' => $request->location,
            'description' => $request->description
        ]);

        // Update order status if delivered
        if ($request->status === 'delivered') {
            $order->status = 'delivered';
            $order->delivered_at = now();
            $order->save();
        }

        return response()->json($tracking);
    }

    public function getTracking($orderId)
    {
        $order = Order::with('deliveryTracking')->findOrFail($orderId);
        
        return response()->json([
            'order_status' => $order->status,
            'delivery_status' => $order->deliveryTracking->last()->status ?? null,
            'tracking_history' => $order->deliveryTracking
        ]);
    }

    public function searchOrders(Request $request)
    {
        $request->validate([
            'query' => 'required|string',
            'status' => 'nullable|in:preparing,picked_up,in_transit,out_for_delivery,delivered',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date'
        ]);

        $orders = Order::with(['deliveryTracking', 'orderItems.product'])
            ->where(function($q) use ($request) {
                $q->where('id', 'like', "%{$request->query}%")
                  ->orWhere('customer_name', 'like', "%{$request->query}%")
                  ->orWhere('customer_phone', 'like', "%{$request->query}%");
            });

        if ($request->status) {
            $orders->whereHas('deliveryTracking', function($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        if ($request->date_from) {
            $orders->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $orders->whereDate('created_at', '<=', $request->date_to);
        }

        return response()->json($orders->paginate(15));
    }
}