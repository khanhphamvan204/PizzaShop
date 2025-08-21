<?php
namespace App\Http\Controllers;

use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductVariantController extends Controller
{
    public function index()
    {
        $variants = ProductVariant::with(['product', 'size', 'crust', 'cartItems', 'orderItems'])->get();
        return response()->json([
            'status' => 'success',
            'data' => $variants
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'size_id' => 'required|exists:sizes,id',
            'crust_id' => 'required|exists:crusts,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $variant = ProductVariant::create($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $variant->load(['product', 'size', 'crust'])
        ], 201);
    }

    public function show(ProductVariant $productVariant)
    {
        return response()->json([
            'status' => 'success',
            'data' => $productVariant->load(['product', 'size', 'crust', 'cartItems', 'orderItems'])
        ], 200);
    }

    public function update(Request $request, ProductVariant $productVariant)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'size_id' => 'required|exists:sizes,id',
            'crust_id' => 'required|exists:crusts,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $productVariant->update($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $productVariant->load(['product', 'size', 'crust'])
        ], 200);
    }

    public function destroy(ProductVariant $productVariant)
    {
        $productVariant->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Product variant deleted successfully'
        ], 200);
    }
}