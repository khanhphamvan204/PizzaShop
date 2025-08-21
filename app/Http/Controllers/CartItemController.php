<?php
namespace App\Http\Controllers;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartItemController extends Controller
{
    public function index()
    {
        $cartItems = CartItem::with(['cart.user', 'productVariant.product', 'productVariant.size', 'productVariant.crust'])->get();
        return response()->json([
            'status' => 'success',
            'data' => $cartItems
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cart_id' => 'required|exists:carts,id',
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $cartItem = CartItem::create($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $cartItem->load(['cart.user', 'productVariant.product', 'productVariant.size', 'productVariant.crust'])
        ], 201);
    }

    public function show(CartItem $cartItem)
    {
        return response()->json([
            'status' => 'success',
            'data' => $cartItem->load(['cart.user', 'productVariant.product', 'productVariant.size', 'productVariant.crust'])
        ], 200);
    }

    public function update(Request $request, CartItem $cartItem)
    {
        $validator = Validator::make($request->all(), [
            'cart_id' => 'required|exists:carts,id',
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $cartItem->update($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $cartItem->load(['cart.user', 'productVariant.product', 'productVariant.size', 'productVariant.crust'])
        ], 200);
    }

    public function destroy(CartItem $cartItem)
    {
        $cartItem->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Cart item deleted successfully'
        ], 200);
    }
}