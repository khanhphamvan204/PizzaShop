<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'variants.size', 'variants.crust', 'reviews'])
                          ->latest()
                          ->paginate(12);
        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:100',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url|max:255',
            'category_id' => 'required|exists:categories,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product = Product::create($request->all());
        return response()->json($product->load('category'), 201);
    }

    public function show($id)
    {
        $product = Product::with(['category', 'variants.size', 'variants.crust', 'reviews.user'])
                         ->findOrFail($id);
        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:100',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url|max:255',
            'category_id' => 'required|exists:categories,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product->update($request->all());
        return response()->json($product->load('category'));
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function getByCategory($categoryId)
    {
        $products = Product::where('category_id', $categoryId)
                          ->with(['variants.size', 'variants.crust'])
                          ->get();
        return response()->json($products);
    }
}
