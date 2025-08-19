<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'items.productVariant.product', 'coupon', 'payment'])
                      ->latest()
                      ->paginate(20);
        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'total_amount' => 'required|numeric|min:0',
            'shipping_address' => 'required|string',
            'coupon_id' => 'nullable|exists:coupons,id',
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id' => $request->user_id,
                'total_amount' => $request->total_amount,
                'shipping_address' => $request->shipping_address,
                'coupon_id' => $request->coupon_id,
                'status' => 'pending'
            ]);

            foreach ($request->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);
            }

            DB::commit();
            return response()->json($order->load(['items.productVariant.product', 'user']), 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to create order'], 500);
        }
    }

    public function show($id)
    {
        $order = Order::with(['user', 'items.productVariant.product.category', 'items.productVariant.size', 'items.productVariant.crust', 'coupon', 'payment'])
                     ->findOrFail($id);
        return response()->json($order);
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,confirmed,shipped,delivered,cancelled',
            'shipping_address' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $order->update($request->only(['status', 'shipping_address']));
        return response()->json($order);
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        
        if (!in_array($order->status, ['pending', 'cancelled'])) {
            return response()->json(['error' => 'Cannot delete confirmed or processed orders'], 422);
        }

        $order->delete();
        return response()->json(['message' => 'Order deleted successfully']);
    }

    public function getUserOrders($userId)
    {
        $orders = Order::where('user_id', $userId)
                      ->with(['items.productVariant.product', 'payment'])
                      ->latest()
                      ->get();
        return response()->json($orders);
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,confirmed,shipped,delivered,cancelled'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $order->update(['status' => $request->status]);
        return response()->json($order);
    }
}
