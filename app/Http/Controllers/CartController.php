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
            return response()->json(['status' => 'error', 'message' => 'User ID or Session ID required'], 422);
        }

        if (!$cart) {
            $cart = Cart::create([
                'user_id' => $userId,
                'session_id' => $sessionId
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $cart->load(['items.productVariant.product', 'items.productVariant.size', 'items.productVariant.crust'])
        ], 200);
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
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        if (!$request->user_id && !$request->session_id) {
            return response()->json(['status' => 'error', 'message' => 'User ID or Session ID required'], 422);
        }

        $cart = Cart::where(function ($query) use ($request) {
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

        return response()->json([
            'status' => 'success',
            'data' => $cartItem->load(['productVariant.product', 'productVariant.size', 'productVariant.crust'])
        ], 201);
    }

    public function updateItem(Request $request, CartItem $cartItem)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $cartItem->update(['quantity' => $request->quantity]);
        return response()->json([
            'status' => 'success',
            'data' => $cartItem->load(['productVariant.product', 'productVariant.size', 'productVariant.crust'])
        ], 200);
    }

    public function removeItem(CartItem $cartItem)
    {
        $cartItem->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Item removed from cart'
        ], 200);
    }

    public function clearCart($userId = null, $sessionId = null)
    {
        $query = Cart::query();

        if ($userId) {
            $cart = $query->where('user_id', $userId)->first();
        } elseif ($sessionId) {
            $cart = $query->where('session_id', $sessionId)->first();
        } else {
            return response()->json(['status' => 'error', 'message' => 'User ID or Session ID required'], 422);
        }

        if ($cart) {
            $cart->items()->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Cart cleared successfully'
        ], 200);
    }
}