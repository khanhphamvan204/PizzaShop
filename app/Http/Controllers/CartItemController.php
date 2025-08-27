<?php
// 21. CartItemController.php
namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartItemController extends Controller
{
    public function index(Request $request)
    {
        $cart = $this->getOrCreateCart($request);

        $query = CartItem::with([
            'productVariant.product',
            'productVariant.size',
            'productVariant.crust',
            'combo'
        ])->where('cart_id', $cart->id);

        if ($request->has('product_id')) {
            $query->whereHas('productVariant.product', function ($q) use ($request) {
                $q->where('id', $request->product_id);
            });
        }

        if ($request->has('combo_id')) {
            $query->where('combo_id', $request->combo_id);
        }

        $cartItems = $query->get();

        // Tính tổng giá trị giỏ hàng
        $total = $this->calculateCartTotal($cartItems);
        $totalItems = $cartItems->sum('quantity');

        return response()->json([
            'cart_id' => $cart->id,
            'items' => $cartItems,
            'total_amount' => $total,
            'total_items' => $totalItems
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'combo_id' => 'nullable|exists:combos,id',
            'quantity' => 'required|integer|min:1'
        ]);

        if (!$request->product_variant_id && !$request->combo_id) {
            return response()->json(['error' => 'Either product_variant_id or combo_id is required'], 400);
        }

        if ($request->product_variant_id && $request->combo_id) {
            return response()->json(['error' => 'Cannot have both product_variant_id and combo_id'], 400);
        }

        $cart = $this->getOrCreateCart($request);

        // Kiểm tra xem item đã tồn tại trong giỏ hàng chưa
        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('product_variant_id', $request->product_variant_id)
            ->where('combo_id', $request->combo_id)
            ->first();

        if ($existingItem) {
            // Nếu đã có, tăng số lượng
            $existingItem->increment('quantity', $request->quantity);
            $cartItem = $existingItem->load([
                'productVariant.product',
                'productVariant.size',
                'productVariant.crust',
                'combo'
            ]);
        } else {
            // Tạo mới
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_variant_id' => $request->product_variant_id,
                'combo_id' => $request->combo_id,
                'quantity' => $request->quantity
            ]);

            $cartItem->load([
                'productVariant.product',
                'productVariant.size',
                'productVariant.crust',
                'combo'
            ]);
        }

        return response()->json([
            'message' => 'Item added to cart successfully',
            'item' => $cartItem
        ], 201);
    }

    public function show($id)
    {
        $cartItem = CartItem::with([
            'cart',
            'productVariant.product',
            'productVariant.size',
            'productVariant.crust',
            'combo'
        ])->findOrFail($id);

        // Kiểm tra quyền truy cập giỏ hàng
        if (!$this->canAccessCartItem($cartItem)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($cartItem);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:99'
        ]);

        $cartItem = CartItem::with('cart')->findOrFail($id);

        // Kiểm tra quyền truy cập
        if (!$this->canAccessCartItem($cartItem)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json([
            'message' => 'Cart item updated successfully',
            'item' => $cartItem->load([
                'productVariant.product',
                'productVariant.size',
                'productVariant.crust',
                'combo'
            ])
        ]);
    }

    public function destroy($id)
    {
        $cartItem = CartItem::with('cart')->findOrFail($id);

        // Kiểm tra quyền truy cập
        if (!$this->canAccessCartItem($cartItem)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $cartItem->delete();

        return response()->json(['message' => 'Item removed from cart successfully']);
    }

    public function increaseQuantity(Request $request, $id)
    {
        $cartItem = CartItem::with('cart')->findOrFail($id);

        if (!$this->canAccessCartItem($cartItem)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($cartItem->quantity >= 99) {
            return response()->json(['error' => 'Maximum quantity reached'], 400);
        }

        $cartItem->increment('quantity');

        return response()->json([
            'message' => 'Quantity increased',
            'item' => $cartItem->load([
                'productVariant.product',
                'combo'
            ])
        ]);
    }

    public function decreaseQuantity(Request $request, $id)
    {
        $cartItem = CartItem::with('cart')->findOrFail($id);

        if (!$this->canAccessCartItem($cartItem)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($cartItem->quantity <= 1) {
            $cartItem->delete();
            return response()->json(['message' => 'Item removed from cart']);
        }

        $cartItem->decrement('quantity');

        return response()->json([
            'message' => 'Quantity decreased',
            'item' => $cartItem->load([
                'productVariant.product',
                'combo'
            ])
        ]);
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:cart_items,id',
            'items.*.quantity' => 'required|integer|min:0|max:99'
        ]);

        DB::beginTransaction();
        try {
            $updatedItems = [];

            foreach ($request->items as $itemData) {
                $cartItem = CartItem::with('cart')->findOrFail($itemData['id']);

                // Kiểm tra quyền truy cập
                if (!$this->canAccessCartItem($cartItem)) {
                    DB::rollback();
                    return response()->json(['error' => 'Unauthorized access to cart item'], 403);
                }

                if ($itemData['quantity'] == 0) {
                    // Xóa item nếu quantity = 0
                    $cartItem->delete();
                } else {
                    // Cập nhật quantity
                    $cartItem->update(['quantity' => $itemData['quantity']]);
                    $updatedItems[] = $cartItem->load([
                        'productVariant.product',
                        'productVariant.size',
                        'productVariant.crust',
                        'combo'
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Cart items updated successfully',
                'items' => $updatedItems
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to update cart items'], 500);
        }
    }

    public function clearByCart(Request $request)
    {
        $cart = $this->getOrCreateCart($request);

        $deletedCount = CartItem::where('cart_id', $cart->id)->delete();

        return response()->json([
            'message' => 'Cart cleared successfully',
            'deleted_items' => $deletedCount
        ]);
    }

    public function moveToWishlist(Request $request, $id)
    {
        $cartItem = CartItem::with('cart')->findOrFail($id);

        if (!$this->canAccessCartItem($cartItem)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // TODO: Implement wishlist functionality
        // For now, just remove from cart
        $cartItem->delete();

        return response()->json([
            'message' => 'Item moved to wishlist (feature coming soon)',
        ]);
    }

    public function duplicateItem(Request $request, $id)
    {
        $cartItem = CartItem::with('cart')->findOrFail($id);

        if (!$this->canAccessCartItem($cartItem)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Tạo bản sao của cart item
        $duplicatedItem = CartItem::create([
            'cart_id' => $cartItem->cart_id,
            'product_variant_id' => $cartItem->product_variant_id,
            'combo_id' => $cartItem->combo_id,
            'quantity' => $cartItem->quantity
        ]);

        return response()->json([
            'message' => 'Item duplicated successfully',
            'item' => $duplicatedItem->load([
                'productVariant.product',
                'productVariant.size',
                'productVariant.crust',
                'combo'
            ])
        ], 201);
    }

    public function getCartSummary(Request $request)
    {
        $cart = $this->getOrCreateCart($request);

        $cartItems = CartItem::where('cart_id', $cart->id)
            ->with([
                'productVariant.product',
                'combo'
            ])
            ->get();

        $summary = [
            'total_items' => $cartItems->sum('quantity'),
            'total_amount' => $this->calculateCartTotal($cartItems),
            'item_count' => $cartItems->count(),
            'has_products' => $cartItems->whereNotNull('product_variant_id')->isNotEmpty(),
            'has_combos' => $cartItems->whereNotNull('combo_id')->isNotEmpty()
        ];

        return response()->json($summary);
    }

    // Helper Methods
    private function getOrCreateCart(Request $request)
    {
        if (Auth::check()) {
            $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        } else {
            $sessionId = $request->session()->getId();
            $cart = Cart::firstOrCreate(['session_id' => $sessionId]);
        }

        return $cart;
    }

    private function canAccessCartItem(CartItem $cartItem)
    {
        $cart = $cartItem->cart;

        if (Auth::check()) {
            return $cart->user_id === Auth::id();
        } else {
            return $cart->session_id === request()->session()->getId();
        }
    }

    private function calculateCartTotal($cartItems)
    {
        $total = 0;

        foreach ($cartItems as $item) {
            if ($item->product_variant_id && $item->productVariant) {
                $total += $item->productVariant->price * $item->quantity;
            } elseif ($item->combo_id && $item->combo) {
                $total += $item->combo->price * $item->quantity;
            }
        }

        return $total;
    }
}
