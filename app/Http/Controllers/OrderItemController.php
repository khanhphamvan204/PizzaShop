<?php
// 20. OrderItemController.php
namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderItemController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = OrderItem::with([
                'order.user',
                'productVariant.product',
                'productVariant.size',
                'productVariant.crust',
                'combo'
            ]);

            if ($request->has('order_id')) {
                $query->where('order_id', $request->order_id);
            }

            if ($request->has('product_id')) {
                $query->whereHas('productVariant.product', function ($q) use ($request) {
                    $q->where('id', $request->product_id);
                });
            }

            if ($request->has('combo_id')) {
                $query->where('combo_id', $request->combo_id);
            }

            if (Auth::check() && Auth::user()->role !== 'admin') {
                $query->whereHas('order', function ($q) {
                    $q->where('user_id', Auth::id());
                });
            }

            $orderItems = $query->get();
            return response()->json($orderItems);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch order items',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|exists:orders,id',
                'product_variant_id' => 'nullable|exists:product_variants,id',
                'combo_id' => 'nullable|exists:combos,id',
                'quantity' => 'required|integer|min:1',
            ]);

            if (!$request->product_variant_id && !$request->combo_id) {
                return response()->json(['error' => 'Either product_variant_id or combo_id is required'], 400);
            }

            if ($request->product_variant_id && $request->combo_id) {
                return response()->json(['error' => 'Cannot have both product_variant_id and combo_id'], 400);
            }

            $order = Order::findOrFail($request->order_id);

            if (Auth::user()->role !== 'admin' && $order->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            if ($order->status !== 'pending') {
                return response()->json(['error' => 'Cannot modify items of non-pending order'], 400);
            }

            // Lấy giá từ combo hoặc product variant
            $price = null;
            if ($request->product_variant_id) {
                $price = \App\Models\ProductVariant::findOrFail($request->product_variant_id)->price;
            } elseif ($request->combo_id) {
                $price = \App\Models\Combo::findOrFail($request->combo_id)->price;
            }

            $orderItem = OrderItem::create([
                'order_id' => $request->order_id,
                'product_variant_id' => $request->product_variant_id,
                'combo_id' => $request->combo_id,
                'quantity' => $request->quantity,
                'price' => $price
            ]);

            return response()->json($orderItem->load([
                'productVariant.product',
                'combo'
            ]), 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create order item',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $orderItem = OrderItem::with([
                'order.user',
                'productVariant.product',
                'productVariant.size',
                'productVariant.crust',
                'combo'
            ])->findOrFail($id);

            if (Auth::user()->role !== 'admin' && $orderItem->order->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            return response()->json($orderItem);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch order item',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $orderItem = OrderItem::with('order')->findOrFail($id);

            if (Auth::user()->role !== 'admin' && $orderItem->order->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            if ($orderItem->order->status !== 'pending') {
                return response()->json(['error' => 'Cannot modify items of non-pending order'], 400);
            }

            $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);

            // Chỉ cập nhật quantity, không cập nhật price
            $orderItem->update($request->only(['quantity']));

            return response()->json($orderItem->load([
                'productVariant.product',
                'combo'
            ]));
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update order item',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $orderItem = OrderItem::with('order')->findOrFail($id);

            if (Auth::user()->role !== 'admin' && $orderItem->order->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            if ($orderItem->order->status !== 'pending') {
                return response()->json(['error' => 'Cannot modify items of non-pending order'], 400);
            }

            $orderItem->delete();

            return response()->json(['message' => 'Order item deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete order item',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getByOrder($orderId)
    {
        try {
            $order = Order::with('user')->findOrFail($orderId);

            if (Auth::user()->role !== 'admin' && $order->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $orderItems = OrderItem::where('order_id', $orderId)
                ->with([
                    'productVariant.product',
                    'productVariant.size',
                    'productVariant.crust',
                    'combo'
                ])
                ->get();

            $totalItems = $orderItems->sum('quantity');
            $totalAmount = $orderItems->sum(function ($item) {
                return $item->quantity * $item->price;
            });

            return response()->json([
                'order' => $order,
                'items' => $orderItems,
                'total_items' => $totalItems,
                'calculated_total' => $totalAmount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch order items by order',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function bestSellingProducts(Request $request)
    {
        try {
            $days = $request->get('days', 30);
            $limit = $request->get('limit', 10);

            $bestSelling = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
                ->join('products', 'product_variants.product_id', '=', 'products.id')
                ->where('orders.created_at', '>=', now()->subDays($days))
                ->whereIn('orders.status', ['confirmed', 'shipped', 'delivered'])
                ->whereNotNull('order_items.product_variant_id')
                ->select(
                    'products.id',
                    'products.name',
                    'products.image_url',
                    DB::raw('SUM(order_items.quantity) as total_quantity'),
                    DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue'),
                    DB::raw('COUNT(DISTINCT orders.id) as order_count')
                )
                ->groupBy('products.id', 'products.name', 'products.image_url')
                ->orderBy('total_quantity', 'desc')
                ->limit($limit)
                ->get();

            return response()->json($bestSelling);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch best selling products',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function bestSellingCombos(Request $request)
    {
        try {
            $days = $request->get('days', 30);
            $limit = $request->get('limit', 10);

            $bestSellingCombos = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('combos', 'order_items.combo_id', '=', 'combos.id')
                ->where('orders.created_at', '>=', now()->subDays($days))
                ->whereIn('orders.status', ['confirmed', 'shipped', 'delivered'])
                ->whereNotNull('order_items.combo_id')
                ->select(
                    'combos.id',
                    'combos.name',
                    'combos.image_url',
                    'combos.price',
                    DB::raw('SUM(order_items.quantity) as total_quantity'),
                    DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue'),
                    DB::raw('COUNT(DISTINCT orders.id) as order_count')
                )
                ->groupBy('combos.id', 'combos.name', 'combos.image_url', 'combos.price')
                ->orderBy('total_quantity', 'desc')
                ->limit($limit)
                ->get();

            return response()->json($bestSellingCombos);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch best selling combos',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
