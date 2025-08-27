<?php
// 9. OrderController.php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.productVariant.product', 'items.combo', 'coupon']);

        if (Auth::user()->role !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('user_id') && Auth::user()->role === 'admin') {
            $query->where('user_id', $request->user_id);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);
        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|string',
            'coupon_code' => 'nullable|string'
        ]);

        if (!Auth::check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        DB::beginTransaction();
        try {
            // Lấy giỏ hàng
            $cart = Cart::where('user_id', Auth::id())->first();
            if (!$cart) {
                return response()->json(['error' => 'Cart is empty'], 400);
            }

            $cartItems = CartItem::where('cart_id', $cart->id)
                ->with(['productVariant', 'combo'])
                ->get();

            if ($cartItems->isEmpty()) {
                return response()->json(['error' => 'Cart is empty'], 400);
            }

            // Tính tổng tiền
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                if ($item->product_variant_id) {
                    $totalAmount += $item->productVariant->price * $item->quantity;
                } elseif ($item->combo_id) {
                    $totalAmount += $item->combo->price * $item->quantity;
                }
            }

            // Áp dụng coupon
            $coupon = null;
            if ($request->coupon_code) {
                $coupon = Coupon::where('code', $request->coupon_code)
                    ->where('is_active', true)
                    ->where('expiry_date', '>=', now())
                    ->first();

                if ($coupon && $totalAmount >= $coupon->min_order_amount) {
                    if ($coupon->discount_percentage) {
                        $discount = ($totalAmount * $coupon->discount_percentage) / 100;
                        if ($coupon->max_discount_amount) {
                            $discount = min($discount, $coupon->max_discount_amount);
                        }
                        $totalAmount -= $discount;
                    } elseif ($coupon->discount_amount) {
                        $totalAmount -= $coupon->discount_amount;
                    }
                }
            }

            // Tạo đơn hàng
            $order = Order::create([
                'user_id' => Auth::id(),
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'shipping_address' => $request->shipping_address,
                'coupon_id' => $coupon ? $coupon->id : null
            ]);

            // Tạo order items
            foreach ($cartItems as $item) {
                $price = $item->product_variant_id ? $item->productVariant->price : $item->combo->price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $item->product_variant_id,
                    'combo_id' => $item->combo_id,
                    'quantity' => $item->quantity,
                    'price' => $price
                ]);
            }

            // Xóa giỏ hàng
            CartItem::where('cart_id', $cart->id)->delete();

            DB::commit();

            return response()->json($order->load(['items', 'coupon']), 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to create order'], 500);
        }
    }

    public function show($id)
    {
        $order = Order::with(['user', 'items.productVariant.product', 'items.combo', 'coupon', 'payments'])
            ->findOrFail($id);

        if (Auth::user()->role !== 'admin' && $order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($order);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,shipped,delivered,cancelled'
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);

        return response()->json($order);
    }

    public function cancel($id)
    {
        $order = Order::findOrFail($id);

        if (Auth::user()->role !== 'admin' && $order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (in_array($order->status, ['shipped', 'delivered'])) {
            return response()->json(['error' => 'Cannot cancel shipped or delivered order'], 400);
        }

        $order->update(['status' => 'cancelled']);

        return response()->json($order);
    }
}