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

        // Nếu không phải admin, chỉ xem order items của mình
        if (Auth::check() && Auth::user()->role !== 'admin') {
            $query->whereHas('order', function ($q) {
                $q->where('user_id', Auth::id());
            });
        }

        $orderItems = $query->paginate(20);
        return response()->json($orderItems);
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'combo_id' => 'nullable|exists:combos,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0'
        ]);

        if (!$request->product_variant_id && !$request->combo_id) {
            return response()->json(['error' => 'Either product_variant_id or combo_id is required'], 400);
        }

        if ($request->product_variant_id && $request->combo_id) {
            return response()->json(['error' => 'Cannot have both product_variant_id and combo_id'], 400);
        }

        $order = Order::findOrFail($request->order_id);

        // Kiểm tra quyền truy cập
        if (Auth::user()->role !== 'admin' && $order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Chỉ cho phép thêm item khi order ở trạng thái pending
        if ($order->status !== 'pending') {
            return response()->json(['error' => 'Cannot modify items of non-pending order'], 400);
        }

        $orderItem = OrderItem::create($request->all());

        return response()->json($orderItem->load([
            'productVariant.product',
            'combo'
        ]), 201);
    }

    public function show($id)
    {
        $orderItem = OrderItem::with([
            'order.user',
            'productVariant.product',
            'productVariant.size',
            'productVariant.crust',
            'combo'
        ])->findOrFail($id);

        // Kiểm tra quyền truy cập
        if (Auth::user()->role !== 'admin' && $orderItem->order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($orderItem);
    }

    public function update(Request $request, $id)
    {
        $orderItem = OrderItem::with('order')->findOrFail($id);

        // Kiểm tra quyền truy cập
        if (Auth::user()->role !== 'admin' && $orderItem->order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Chỉ cho phép cập nhật khi order ở trạng thái pending
        if ($orderItem->order->status !== 'pending') {
            return response()->json(['error' => 'Cannot modify items of non-pending order'], 400);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0'
        ]);

        $orderItem->update($request->only(['quantity', 'price']));

        return response()->json($orderItem->load([
            'productVariant.product',
            'combo'
        ]));
    }

    public function destroy($id)
    {
        $orderItem = OrderItem::with('order')->findOrFail($id);

        // Kiểm tra quyền truy cập
        if (Auth::user()->role !== 'admin' && $orderItem->order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Chỉ cho phép xóa khi order ở trạng thái pending
        if ($orderItem->order->status !== 'pending') {
            return response()->json(['error' => 'Cannot modify items of non-pending order'], 400);
        }

        $orderItem->delete();

        return response()->json(['message' => 'Order item deleted successfully']);
    }

    public function getByOrder($orderId)
    {
        $order = Order::with('user')->findOrFail($orderId);

        // Kiểm tra quyền truy cập
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

        // Tính toán thống kê
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
    }

    public function bulkUpdate(Request $request, $orderId)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'nullable|exists:order_items,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.combo_id' => 'nullable|exists:combos,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0'
        ]);

        $order = Order::findOrFail($orderId);

        // Kiểm tra quyền truy cập
        if (Auth::user()->role !== 'admin' && $order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Chỉ cho phép cập nhật khi order ở trạng thái pending
        if ($order->status !== 'pending') {
            return response()->json(['error' => 'Cannot modify items of non-pending order'], 400);
        }

        DB::beginTransaction();
        try {
            foreach ($request->items as $itemData) {
                if (isset($itemData['id'])) {
                    // Update existing item
                    $orderItem = OrderItem::where('id', $itemData['id'])
                        ->where('order_id', $orderId)
                        ->firstOrFail();

                    $orderItem->update([
                        'quantity' => $itemData['quantity'],
                        'price' => $itemData['price']
                    ]);
                } else {
                    // Create new item
                    if (!isset($itemData['product_variant_id']) && !isset($itemData['combo_id'])) {
                        throw new \Exception('Either product_variant_id or combo_id is required');
                    }

                    OrderItem::create([
                        'order_id' => $orderId,
                        'product_variant_id' => $itemData['product_variant_id'] ?? null,
                        'combo_id' => $itemData['combo_id'] ?? null,
                        'quantity' => $itemData['quantity'],
                        'price' => $itemData['price']
                    ]);
                }
            }

            DB::commit();

            $updatedItems = OrderItem::where('order_id', $orderId)
                ->with([
                    'productVariant.product',
                    'productVariant.size',
                    'productVariant.crust',
                    'combo'
                ])
                ->get();

            return response()->json([
                'message' => 'Order items updated successfully',
                'items' => $updatedItems
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to update order items: ' . $e->getMessage()], 500);
        }
    }

    // Thống kê sản phẩm bán chạy
    public function bestSellingProducts(Request $request)
    {
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
    }

    // Thống kê combo bán chạy
    public function bestSellingCombos(Request $request)
    {
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
    }
}
