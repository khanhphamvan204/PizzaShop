<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Combo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $cart = $this->getUserCart($user->id);

            $cartItems = CartItem::where('cart_id', $cart->id)
                ->with([
                    'productVariant.product.category',
                    'productVariant.size',
                    'productVariant.crust',
                    'combo'
                ])
                ->get();

            $total = $this->calculateCartTotal($cartItems);
            $formattedItems = $this->formatCartItemsForDisplay($cartItems);

            return response()->json([
                'success' => true,
                'cart' => $cart,
                'items' => $formattedItems,
                'total' => $total
            ]);
        } catch (\Exception $e) {
            Log::error('Get cart error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get cart'], 500);
        }
    }

    public function addItem(Request $request)
    {
        try {
            $request->validate([
                'product_variant_id' => 'nullable|exists:product_variants,id',
                'combo_id' => 'nullable|exists:combos,id',
                'quantity' => 'required|integer|min:1|max:10'
            ]);

            $userId = $request->user()->id;

            if (!$request->product_variant_id && !$request->combo_id) {
                return response()->json(['success' => false, 'error' => 'Either product_variant_id or combo_id is required'], 400);
            }

            if ($request->product_variant_id && $request->combo_id) {
                return response()->json(['success' => false, 'error' => 'Cannot add both product and combo'], 400);
            }

            return DB::transaction(function () use ($request, $userId) {
                if ($request->product_variant_id) {
                    $variant = ProductVariant::lockForUpdate()->find($request->product_variant_id);
                    if (!$variant || $variant->stock < $request->quantity) {
                        return response()->json(['success' => false, 'error' => 'Not enough stock'], 400);
                    }
                }

                $cart = $this->getUserCart($userId);

                $existingItem = CartItem::where('cart_id', $cart->id)
                    ->where('product_variant_id', $request->product_variant_id)
                    ->where('combo_id', $request->combo_id)
                    ->first();

                if ($existingItem) {
                    $newQuantity = $existingItem->quantity + $request->quantity;

                    if ($request->product_variant_id) {
                        if ($variant->stock < $newQuantity) {
                            return response()->json(['success' => false, 'error' => 'Not enough stock for requested quantity'], 400);
                        }
                    }

                    $existingItem->update(['quantity' => $newQuantity]);
                    $cartItem = $existingItem;
                } else {
                    $cartItem = CartItem::create([
                        'cart_id' => $cart->id,
                        'product_variant_id' => $request->product_variant_id,
                        'combo_id' => $request->combo_id,
                        'quantity' => $request->quantity
                    ]);
                }

                $cartItem->load(['productVariant.product', 'combo']);

                return response()->json([
                    'success' => true,
                    'message' => 'Item added to cart successfully',
                    'item' => $cartItem
                ], 201);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Add item to cart error: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'payload' => $request->all()
            ]);
            return response()->json(['success' => false, 'error' => 'Failed to add item to cart'], 500);
        }
    }


    public function updateItem(Request $request, $itemId)
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:1|max:10'
            ]);

            $userId = $request->user()->id;
            $cart = $this->getUserCart($userId);

            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('id', $itemId)
                ->with(['productVariant'])
                ->firstOrFail();

            if ($cartItem->product_variant_id) {
                if ($cartItem->productVariant->stock < $request->quantity) {
                    return response()->json(['error' => 'Not enough stock'], 400);
                }
            }

            $cartItem->update(['quantity' => $request->quantity]);

            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully',
                'item' => $cartItem
            ]);
        } catch (\Exception $e) {
            Log::error('Update cart item error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update item'], 500);
        }
    }

    public function removeItem(Request $request, $itemId)
    {
        try {
            $userId = $request->user()->id;
            $cart = $this->getUserCart($userId);

            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('id', $itemId)
                ->firstOrFail();

            $cartItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart'
            ]);
        } catch (\Exception $e) {
            Log::error('Remove cart item error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to remove item'], 500);
        }
    }
    public function clear(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $cart = $this->getUserCart($userId);

            CartItem::where('cart_id', $cart->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cart cleared successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Clear cart error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to clear cart'], 500);
        }
    }
    private function getUserCart($userId)
    {
        return Cart::firstOrCreate(['user_id' => $userId]);
    }
    private function calculateCartTotal($cartItems)
    {
        $total = 0;
        foreach ($cartItems as $item) {
            if ($item->product_variant_id) {
                $total += $item->productVariant->price * $item->quantity;
            } elseif ($item->combo_id) {
                $total += $item->combo->price * $item->quantity;
            }
        }
        return $total;
    }


    private function formatCartItemsForDisplay($cartItems)
    {
        $formatted = [];

        foreach ($cartItems as $item) {
            if ($item->product_variant_id) {
                $formatted[] = [
                    'id' => $item->id,
                    'type' => 'product',
                    'product_name' => $item->productVariant->product->name,
                    'variant_info' => [
                        'size' => $item->productVariant->size->name ?? null,
                        'crust' => $item->productVariant->crust->name ?? null
                    ],
                    'price' => $item->productVariant->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->productVariant->price * $item->quantity,
                    'image_url' => $item->productVariant->product->image_url
                ];
            } elseif ($item->combo_id) {
                $formatted[] = [
                    'id' => $item->id,
                    'type' => 'combo',
                    'combo_name' => $item->combo->name,
                    'price' => $item->combo->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->combo->price * $item->quantity,
                    'image_url' => $item->combo->image_url
                ];
            }
        }

        return $formatted;
    }
}