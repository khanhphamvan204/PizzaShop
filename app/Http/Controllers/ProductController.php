<?php
namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'variants.size', 'variants.crust', 'reviews'])->get();
        return response()->json([
            'status' => 'success',
            'data' => $products
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::create($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $product->load(['category', 'variants', 'reviews'])
        ], 201);
    }

    public function show(Product $product)
    {
        return response()->json([
            'status' => 'success',
            'data' => $product->load(['category', 'variants.size', 'variants.crust', 'reviews'])
        ], 200);
    }

    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $product->update($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $product->load(['category', 'variants', 'reviews'])
        ], 200);
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Product deleted successfully'
        ], 200);
    }
    public function featured()
    {
        $products = Product::query()
            ->select('products.*')
            ->selectRaw('SUM(order_items.quantity) as total_sold') // Tính tổng số lượng bán
            ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->join('order_items', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->groupBy('products.id')
            ->orderByDesc('total_sold') // Sắp xếp theo tổng số lượng bán
            ->with(['category', 'variants.size', 'variants.crust', 'reviews'])
            ->limit(5) // Giới hạn 10 sản phẩm
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $products
        ], 200);
    }
}