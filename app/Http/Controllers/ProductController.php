<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Product::with(['category', 'productVariants.size', 'productVariants.crust']);

            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $products = $query->get();
            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch products',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'variants' => 'required|array',
            'variants.*.size_id' => 'nullable|exists:sizes,id',
            'variants.*.crust_id' => 'nullable|exists:crusts,id',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.stock' => 'integer|min:0'
        ]);

        DB::beginTransaction();
        try {
            $product = Product::create($request->only(['name', 'description', 'image_url', 'category_id']));

            foreach ($request->variants as $variantData) {
                $product->productVariants()->create($variantData);
            }

            DB::commit();
            return response()->json($product->load('productVariants'), 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Failed to create product',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $product = Product::with(['category', 'productVariants.size', 'productVariants.crust', 'reviews.user'])
                ->findOrFail($id);
            return response()->json($product);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch product',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:100',
                'description' => 'nullable|string',
                'image_url' => 'nullable|string|max:255',
                'category_id' => 'nullable|exists:categories,id'
            ]);

            $product->update($request->only(['name', 'description', 'image_url', 'category_id']));
            return response()->json($product);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update product',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();
            return response()->json(['message' => 'Product deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete product',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function featured()
    {
        try {
            $products = Product::with(['category', 'productVariants'])
                ->whereHas('productVariants', function ($query) {
                    $query->where('stock', '>', 0);
                })
                ->limit(8)
                ->get();
            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch featured products',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
