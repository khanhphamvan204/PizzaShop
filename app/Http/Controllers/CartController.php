<?php
// CartController.php
namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Combo;
use App\Services\TextToSqlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    protected $textToSqlService;

    public function __construct(TextToSqlService $textToSqlService)
    {
        $this->textToSqlService = $textToSqlService;
    }

    /**
     * Tìm kiếm sản phẩm bằng natural language
     */
    public function searchProducts(Request $request)
    {
        try {
            $query = $request->input('query');

            if (empty($query)) {
                return response()->json(['error' => 'Query is required'], 400);
            }

            // Sử dụng text-to-SQL để tìm sản phẩm
            $products = $this->textToSqlService->searchProducts($query);

            // Format dữ liệu cho frontend
            $formattedProducts = $this->formatProductsForDisplay($products);

            return response()->json([
                'success' => true,
                'products' => $formattedProducts,
                'total' => count($formattedProducts)
            ]);

        } catch (\Exception $e) {
            Log::error('Search products error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to search products'], 500);
        }
    }

    /**
     * Lấy tất cả sản phẩm có thể thêm vào giỏ hàng
     */
    public function getAllProducts()
    {
        try {
            $products = Product::with(['category', 'productVariants.size', 'productVariants.crust'])
                ->whereHas('productVariants')
                ->get();

            $combos = Combo::where('is_active', true)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->get();

            $formattedProducts = $this->formatProductsForDisplay($products);
            $formattedCombos = $this->formatCombosForDisplay($combos);

            return response()->json([
                'success' => true,
                'products' => $formattedProducts,
                'combos' => $formattedCombos
            ]);

        } catch (\Exception $e) {
            Log::error('Get all products error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get products'], 500);
        }
    }

    /**
     * Lấy giỏ hàng
     */
    public function index(Request $request)
    {
        try {
            $cart = $this->getOrCreateCart($request);

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

    /**
     * Thêm sản phẩm vào giỏ hàng
     */
    public function addItem(Request $request)
    {
        try {
            $request->validate([
                'product_variant_id' => 'nullable|exists:product_variants,id',
                'combo_id' => 'nullable|exists:combos,id',
                'quantity' => 'required|integer|min:1|max:10'
            ]);

            if (!$request->product_variant_id && !$request->combo_id) {
                return response()->json(['error' => 'Either product_variant_id or combo_id is required'], 400);
            }

            if ($request->product_variant_id && $request->combo_id) {
                return response()->json(['error' => 'Cannot add both product and combo'], 400);
            }

            // Kiểm tra stock nếu là product variant
            if ($request->product_variant_id) {
                $variant = ProductVariant::find($request->product_variant_id);
                if ($variant->stock < $request->quantity) {
                    return response()->json(['error' => 'Not enough stock'], 400);
                }
            }

            $cart = $this->getOrCreateCart($request);

            // Kiểm tra item đã tồn tại
            $existingItem = CartItem::where('cart_id', $cart->id)
                ->where('product_variant_id', $request->product_variant_id)
                ->where('combo_id', $request->combo_id)
                ->first();

            if ($existingItem) {
                $newQuantity = $existingItem->quantity + $request->quantity;

                // Kiểm tra stock cho quantity mới
                if ($request->product_variant_id) {
                    $variant = ProductVariant::find($request->product_variant_id);
                    if ($variant->stock < $newQuantity) {
                        return response()->json(['error' => 'Not enough stock for requested quantity'], 400);
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

        } catch (\Exception $e) {
            Log::error('Add item to cart error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to add item to cart'], 500);
        }
    }

    /**
     * Cập nhật quantity của item trong giỏ hàng
     */
    public function updateItem(Request $request, $itemId)
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:1|max:10'
            ]);

            $cart = $this->getOrCreateCart($request);
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('id', $itemId)
                ->with(['productVariant'])
                ->firstOrFail();

            // Kiểm tra stock nếu là product variant
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

    /**
     * Xóa item khỏi giỏ hàng
     */
    public function removeItem(Request $request, $itemId)
    {
        try {
            $cart = $this->getOrCreateCart($request);
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

    /**
     * Xóa toàn bộ giỏ hàng
     */
    public function clear(Request $request)
    {
        try {
            $cart = $this->getOrCreateCart($request);
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

    /**
     * Tạo hoặc lấy cart
     */
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

    /**
     * Tính tổng tiền giỏ hàng
     */
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

    /**
     * Format products cho display
     */
    private function formatProductsForDisplay($products)
    {
        $formatted = [];

        foreach ($products as $product) {
            foreach ($product->productVariants as $variant) {
                $formatted[] = [
                    'id' => $product->id,
                    'variant_id' => $variant->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'image_url' => $product->image_url,
                    'category' => $product->category->name ?? 'N/A',
                    'size' => $variant->size->name ?? null,
                    'crust' => $variant->crust->name ?? null,
                    'price' => $variant->price,
                    'stock' => $variant->stock,
                    'type' => 'product'
                ];
            }
        }

        return $formatted;
    }

    /**
     * Format combos cho display
     */
    private function formatCombosForDisplay($combos)
    {
        $formatted = [];

        foreach ($combos as $combo) {
            $formatted[] = [
                'id' => $combo->id,
                'name' => $combo->name,
                'description' => $combo->description,
                'image_url' => $combo->image_url,
                'price' => $combo->price,
                'start_date' => $combo->start_date,
                'end_date' => $combo->end_date,
                'type' => 'combo'
            ];
        }

        return $formatted;
    }

    /**
     * Format cart items cho display
     */
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