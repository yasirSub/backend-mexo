<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentManagementController extends Controller
{
    /**
     * Get all payments
     */
    public function index(Request $request)
    {
        $query = Payment::with(['order.seller', 'order.user', 'seller']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            if ($request->status === 'paid_to_seller') {
                $query->where('seller_paid', true);
            } else {
                $query->where('status', $request->status);
            }
        }

        // Filter by seller
        if ($request->has('seller_id')) {
            $query->where(function($q) use ($request) {
                $q->where('seller_id', $request->seller_id)
                  ->orWhereHas('order', function ($orderQuery) use ($request) {
                      $orderQuery->where('seller_id', $request->seller_id);
                  });
            });
        }

        // Date range filter
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $perPage = $request->query('per_page', 50);
        $payments = $query->latest()->get();

        // Map payments to include seller_id from order if not set
        $payments = $payments->map(function ($payment) {
            if (!$payment->seller_id && $payment->order && $payment->order->seller_id) {
                $payment->seller_id = $payment->order->seller_id;
            }
            return $payment;
        });

        return response()->json([
            'success' => true,
            'data' => $payments->values()->all(),
        ], 200);
    }

    /**
     * Get payment statistics
     */
    public function statistics()
    {
        $totalPayments = Payment::where('status', 'completed')
            ->orWhere('status', 'paid_to_seller')
            ->sum('amount');
        $pendingPayments = Payment::where('status', 'pending')->sum('amount');
        $failedPayments = Payment::where('status', 'failed')->sum('amount');
        $paidToSeller = Payment::where('seller_paid', true)->sum('amount');
        
        $todayPayments = Payment::where(function($query) {
                $query->where('status', 'completed')
                      ->orWhere('status', 'paid_to_seller');
            })
            ->whereDate('created_at', today())
            ->sum('amount');

        $thisMonthPayments = Payment::where(function($query) {
                $query->where('status', 'completed')
                      ->orWhere('status', 'paid_to_seller');
            })
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'total_payments' => $totalPayments,
                'pending_payments' => $pendingPayments,
                'failed_payments' => $failedPayments,
                'paid_to_seller' => $paidToSeller,
                'today_payments' => $todayPayments,
                'this_month_payments' => $thisMonthPayments,
            ],
        ], 200);
    }

    /**
     * Get seller payouts (pending payouts to sellers)
     */
    public function sellerPayouts(Request $request)
    {
        $query = Seller::withSum([
            'orders as total_revenue' => function ($query) {
                $query->whereIn('status', ['delivered', 'completed']);
            }
        ], 'total_amount')
        ->withSum([
            'orders as pending_payout' => function ($query) {
                $query->where('status', 'delivered')
                      ->where('payment_status', 'pending');
            }
        ], 'total_amount');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $sellers = $query->paginate($request->query('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $sellers->items(),
            'meta' => [
                'current_page' => $sellers->currentPage(),
                'per_page' => $sellers->perPage(),
                'total' => $sellers->total(),
                'last_page' => $sellers->lastPage(),
            ],
        ], 200);
    }

    /**
     * Get single payment details
     */
    public function show($id)
    {
        $payment = Payment::with(['order.seller', 'order.user', 'order.items.product'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $payment,
        ], 200);
    }

    /**
     * Update payment status
     */
    public function updateStatus(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,completed,failed,refunded,paid_to_seller',
            'notes' => 'nullable|string',
        ]);

        $payment->update([
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment status updated successfully',
            'data' => $payment,
        ], 200);
    }

    /**
     * Mark payment as paid to seller
     */
    public function markPaid($id)
    {
        try {
            $payment = Payment::with('order')->findOrFail($id);

            // Only allow marking completed payments as paid to seller
            if ($payment->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only completed payments can be marked as paid to seller',
                ], 400);
            }

            // Get seller_id from order if not set
            if (!$payment->seller_id && $payment->order) {
                $payment->seller_id = $payment->order->seller_id;
            }

            $payment->update([
                'seller_paid' => true,
                'seller_paid_at' => now(),
                'status' => 'paid_to_seller',
            ]);

            // Update order payout status if exists
            if ($payment->order) {
                $payment->order->update([
                    'payout_status' => 'paid',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment marked as paid to seller successfully',
                'data' => $payment->load(['order.seller', 'order.user', 'seller']),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking payment as paid: ' . $e->getMessage(),
            ], 500);
        }
    }
}
