<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:1'
        ]);

        $order = Order::findOrFail($request->order_id);
        
        $paymentIntent = PaymentIntent::create([
            'amount' => $request->amount * 100, // Convert to cents
            'currency' => 'usd',
            'metadata' => [
                'order_id' => $order->id,
                'seller_id' => $order->seller_id
            ]
        ]);

        return response()->json([
            'clientSecret' => $paymentIntent->client_secret
        ]);
    }

    public function handleWebhook(Request $request)
    {
        $payload = $request->all();
        $event = null;

        try {
            $event = \Stripe\Event::constructFrom($payload);
        } catch(\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                $orderId = $paymentIntent->metadata->order_id;
                
                $order = Order::find($orderId);
                if ($order) {
                    $order->status = 'paid';
                    $order->save();

                    Payment::create([
                        'order_id' => $orderId,
                        'amount' => $paymentIntent->amount / 100,
                        'transaction_id' => $paymentIntent->id,
                        'status' => 'completed'
                    ]);
                }
                break;
            
            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                $orderId = $paymentIntent->metadata->order_id;
                
                $order = Order::find($orderId);
                if ($order) {
                    $order->status = 'payment_failed';
                    $order->save();

                    Payment::create([
                        'order_id' => $orderId,
                        'amount' => $paymentIntent->amount / 100,
                        'transaction_id' => $paymentIntent->id,
                        'status' => 'failed'
                    ]);
                }
                break;
        }

        return response()->json(['status' => 'success']);
    }

    public function getPaymentStatus(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        $payment = Payment::where('order_id', $orderId)->latest()->first();

        return response()->json([
            'order_status' => $order->status,
            'payment_status' => $payment ? $payment->status : null,
            'amount' => $payment ? $payment->amount : null,
            'transaction_id' => $payment ? $payment->transaction_id : null,
            'created_at' => $payment ? $payment->created_at : null
        ]);
    }
}