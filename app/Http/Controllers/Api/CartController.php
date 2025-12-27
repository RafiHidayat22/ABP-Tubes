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
                'total' => $total
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
            $cart->quantity += $validated['quantity'];
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
                'message' => 'Insufficient stock'
            ], 400);
        }

        $cart->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated',
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

        $cart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart item removed'
        ]);
    }
}
