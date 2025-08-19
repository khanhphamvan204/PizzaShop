<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function index($userId = null, $sessionId = null)
    {
        $query = Cart::with(['items.productVariant.product', 'items.productVariant.size', 'items.productVariant.crust']);
        
        if ($userId) {
            $cart = $query->where('user_id', $userId)->first();
        } elseif ($sessionId) {
            $cart = $query->where('session_id', $sessionId)->first();
        } else {
            return response()->json(['error' => 'User ID or Session ID required'], 422);
        }

        if (!$cart) {
            $cart = Cart::create([
                'user_id' => $userId,
                'session_id' => $sessionId
            ]);
        }

        return response()->json($cart);
    }

    public function addItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'session_id' => 'nullable|string',
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!$request->user_id && !$request->session_id) {
            return response()->json(['error' => 'User ID or Session ID required'], 422);
        }

        // Find or create cart
        $cart = Cart::where(function($query) use ($request) {
            if ($request->user_id) {
                $query->where('user_id', $request->user_id);
            } else {
                $query->where('session_id', $request->session_id);
            }
        })->first();

        if (!$cart) {
            $cart = Cart::create([
                'user_id' => $request->user_id,
                'session_id' => $request->session_id
            ]);
        }

        // Check if item already exists in cart
        $existingItem = CartItem::where([
            'cart_id' => $cart->id,
            'product_variant_id' => $request->product_variant_id
        ])->first();

        if ($existingItem) {
            $existingItem->update([
                'quantity' => $existingItem->quantity + $request->quantity
            ]);
            $cartItem = $existingItem;
        } else {
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_variant_id' => $request->product_variant_id,
                'quantity' => $request->quantity
            ]);
        }

        return response()->json($cartItem->load(['productVariant.product', 'productVariant.size', 'productVariant.crust']), 201);
    }

    public function updateItem(Request $request, $itemId)
    {
        $cartItem = CartItem::findOrFail($itemId);

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cartItem->update(['quantity' => $request->quantity]);
        return response()->json($cartItem);
    }

    public function removeItem($itemId)
    {
        $cartItem = CartItem::findOrFail($itemId);
        $cartItem->delete();
        return response()->json(['message' => 'Item removed from cart']);
    }

    public function clearCart($userId = null, $sessionId = null)
    {
        $query = Cart::query();
        
        if ($userId) {
            $cart = $query->where('user_id', $userId)->first();
        } elseif ($sessionId) {
            $cart = $query->where('session_id', $sessionId)->first();
        } else {
            return response()->json(['error' => 'User ID or Session ID required'], 422);
        }

        if ($cart) {
            $cart->items()->delete();
        }

        return response()->json(['message' => 'Cart cleared successfully']);
    }
}