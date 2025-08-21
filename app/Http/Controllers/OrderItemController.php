<?php
namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderItemController extends Controller
{
    public function index()
    {
        $orderItems = OrderItem::with(['order.user', 'productVariant.product', 'productVariant.size', 'productVariant.crust'])->get();
        return response()->json([
            'status' => 'success',
            'data' => $orderItems
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $orderItem = OrderItem::create($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $orderItem->load(['order.user', 'productVariant.product', 'productVariant.size', 'productVariant.crust'])
        ], 201);
    }

    public function show(OrderItem $orderItem)
    {
        return response()->json([
            'status' => 'success',
            'data' => $orderItem->load(['order.user', 'productVariant.product', 'productVariant.size', 'productVariant.crust'])
        ], 200);
    }

    public function update(Request $request, OrderItem $orderItem)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $orderItem->update($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $orderItem->load(['order.user', 'productVariant.product', 'productVariant.size', 'productVariant.crust'])
        ], 200);
    }

    public function destroy(OrderItem $orderItem)
    {
        $orderItem->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Order item deleted successfully'
        ], 200);
    }
}