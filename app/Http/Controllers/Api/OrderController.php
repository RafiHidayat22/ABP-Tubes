<?php
// app/Http/Controllers/Api/OrderController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Midtrans\Config;
use Midtrans\Snap;

class OrderController extends Controller
{
    public function __construct()
    {
        // Set Midtrans configuration
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = config('services.midtrans.is_sanitized');
        Config::$is3ds = config('services.midtrans.is_3ds');
    }

    public function index(Request $request)
    {
        $orders = Order::with('items.product')
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    public function show(Request $request, $id)
    {
        $order = Order::with('items.product')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'shipping_address' => 'required|string',
            'phone' => 'required|string'
        ]);

        $user = $request->user();
        $carts = Cart::with('product')
            ->where('user_id', $user->id)
            ->get();

        if ($carts->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ], 400);
        }

        // Check stock availability
        foreach ($carts as $cart) {
            if ($cart->product->stock < $cart->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock for {$cart->product->name}"
                ], 400);
            }
        }

        DB::beginTransaction();
        try {
            // Calculate total
            $totalAmount = $carts->sum(function ($cart) {
                return $cart->product->price * $cart->quantity;
            });

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => Order::generateOrderNumber(),
                'total_amount' => $totalAmount,
                'shipping_address' => $validated['shipping_address'],
                'phone' => $validated['phone']
            ]);

            // Create order items and reduce stock
            foreach ($carts as $cart) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cart->product_id,
                    'quantity' => $cart->quantity,
                    'price' => $cart->product->price
                ]);

                // Reduce stock
                $cart->product->decrement('stock', $cart->quantity);
            }

            // Create Midtrans transaction
            $params = [
                'transaction_details' => [
                    'order_id' => $order->order_number,
                    'gross_amount' => (int) $totalAmount,
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $validated['phone'],
                ],
                'item_details' => $carts->map(function ($cart) {
                    return [
                        'id' => $cart->product_id,
                        'price' => (int) $cart->product->price,
                        'quantity' => $cart->quantity,
                        'name' => $cart->product->name,
                    ];
                })->toArray()
            ];

            $snapToken = Snap::getSnapToken($params);
            $order->update(['snap_token' => $snapToken]);

            // Clear cart
            Cart::where('user_id', $user->id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'order' => $order->load('items.product'),
                    'snap_token' => $snapToken
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function webhook(Request $request)
    {
        $serverKey = config('services.midtrans.server_key');
        $hashed = hash('sha512', $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        if ($hashed !== $request->signature_key) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature'
            ], 403);
        }

        $order = Order::where('order_number', $request->order_id)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $transactionStatus = $request->transaction_status;
        $fraudStatus = $request->fraud_status ?? '';

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'accept') {
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'processing',
                    'payment_type' => $request->payment_type
                ]);
            }
        } elseif ($transactionStatus == 'settlement') {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'processing',
                'payment_type' => $request->payment_type
            ]);
        } elseif ($transactionStatus == 'pending') {
            $order->update([
                'payment_status' => 'pending',
                'payment_type' => $request->payment_type
            ]);
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            $order->update([
                'payment_status' => 'failed',
                'status' => 'cancelled'
            ]);
        }

        return response()->json(['success' => true]);
    }
}