<?php
namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'coupon', 'orderItems.productVariant', 'payment'])->get();
        return response()->json([
            'status' => 'success',
            'data' => $orders
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,confirmed,shipped,delivered,cancelled',
            'shipping_address' => 'nullable|string',
            'coupon_id' => 'nullable|exists:coupons,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::create($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $order->load(['user', 'coupon', 'orderItems', 'payment'])
        ], 201);
    }

    public function show(Order $order)
    {
        return response()->json([
            'status' => 'success',
            'data' => $order->load(['user', 'coupon', 'orderItems.productVariant', 'payment'])
        ], 200);
    }

    public function update(Request $request, Order $order)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,confirmed,shipped,delivered,cancelled',
            'shipping_address' => 'nullable|string',
            'coupon_id' => 'nullable|exists:coupons,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $order->update($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $order->load(['user', 'coupon', 'orderItems', 'payment'])
        ], 200);
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Order deleted successfully'
        ], 200);
    }
}