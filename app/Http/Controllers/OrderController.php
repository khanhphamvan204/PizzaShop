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
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Order::with(['user', 'items.productVariant.product', 'items.combo', 'coupon', 'payments']);

            if (Auth::user()->role !== 'admin') {
                $query->where('user_id', Auth::id());
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('user_id') && Auth::user()->role === 'admin') {
                $query->where('user_id', $request->user_id);
            }

            $orders = $query->orderBy('created_at', 'desc')->get();

            // Thêm thông tin payment method vào mỗi order
            $orders->each(function ($order) {
                $completedPayment = $order->payments->where('status', 'completed')->first();
                $order->payment_method = $completedPayment ? $completedPayment->method : null;
                $order->payment_status = $completedPayment ? $completedPayment->status : 'pending';
            });

            return response()->json($orders);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch orders',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'shipping_address' => 'required|string',
                'coupon_code' => 'nullable|string'
            ]);

            if (!Auth::check()) {
                return response()->json(['error' => 'Authentication required'], 401);
            }

            DB::beginTransaction();

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

                if ($coupon) {
                    if ($totalAmount >= $coupon->min_order_amount) {
                        if ($coupon->discount_percentage) {
                            $discount = ($totalAmount * $coupon->discount_percentage) / 100;
                            if ($coupon->max_discount_amount) {
                                $discount = min($discount, $coupon->max_discount_amount);
                            }
                            $totalAmount -= $discount;
                        } elseif ($coupon->discount_amount) {
                            $discount = $coupon->discount_amount;
                            if ($coupon->max_discount_amount) {
                                $discount = min($discount, $coupon->max_discount_amount);
                            }
                            $totalAmount -= $discount;
                        }
                    } else {
                        return response()->json(['error' => 'Order amount does not meet coupon minimum'], 400);
                    }
                } else {
                    return response()->json(['error' => 'Invalid or expired coupon'], 400);
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
        } catch (ValidationException $ve) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Failed to create order',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $order = Order::with(['user', 'items.productVariant.product', 'items.combo', 'coupon', 'payments'])
                ->findOrFail($id);

            if (Auth::user()->role !== 'admin' && $order->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            return response()->json($order);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch order',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,confirmed,shipped,delivered,cancelled'
            ]);

            $order = Order::findOrFail($id);
            $order->update(['status' => $request->status]);

            return response()->json($order);
        } catch (ValidationException $ve) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update status',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function cancel($id)
    {
        try {
            $order = Order::findOrFail($id);

            if (Auth::user()->role !== 'admin' && $order->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            if (in_array($order->status, ['shipped', 'delivered'])) {
                return response()->json(['error' => 'Cannot cancel shipped or delivered order'], 400);
            }

            $order->update(['status' => 'cancelled']);

            return response()->json($order);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to cancel order',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
