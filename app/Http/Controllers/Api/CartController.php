<?php
// app/Http/Controllers/Api/CartController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $carts = Cart::with('product')
            ->where('user_id', $request->user()->id)
            ->get();

        $total = $carts->sum(function ($cart) {
            return $cart->product->price * $cart->quantity;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $carts,
                'total' => $total,
                'total_items' => $carts->sum('quantity')
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::find($validated['product_id']);

        if ($product->stock < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock'
            ], 400);
        }

        $cart = Cart::where('user_id', $request->user()->id)
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($cart) {
            $newQuantity = $cart->quantity + $validated['quantity'];
            
            if ($product->stock < $newQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock. Available stock: ' . $product->stock
                ], 400);
            }
            
            $cart->quantity = $newQuantity;
            $cart->save();
        } else {
            $cart = Cart::create([
                'user_id' => $request->user()->id,
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity']
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart',
            'data' => $cart->load('product')
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = Cart::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found'
            ], 404);
        }

        if ($cart->product->stock < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock. Available stock: ' . $cart->product->stock
            ], 400);
        }

        $cart->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully',
            'data' => $cart->load('product')
        ]);
    }

    public function increment(Request $request, $id)
    {
        $cart = Cart::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found'
            ], 404);
        }

        $newQuantity = $cart->quantity + 1;

        if ($cart->product->stock < $newQuantity) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot add more. Available stock: ' . $cart->product->stock
            ], 400);
        }

        $cart->increment('quantity');
        $cart->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Quantity increased',
            'data' => $cart->load('product')
        ]);
    }

    public function decrement(Request $request, $id)
    {
        $cart = Cart::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found'
            ], 404);
        }

        if ($cart->quantity <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Quantity cannot be less than 1. Use delete endpoint to remove item.'
            ], 400);
        }

        $cart->decrement('quantity');
        $cart->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Quantity decreased',
            'data' => $cart->load('product')
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $cart = Cart::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found'
            ], 404);
        }

        $productName = $cart->product->name;
        $cart->delete();

        return response()->json([
            'success' => true,
            'message' => "{$productName} removed from cart"
        ]);
    }

    public function clear(Request $request)
    {
        $deleted = Cart::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully',
            'deleted_items' => $deleted
        ]);
    }
}