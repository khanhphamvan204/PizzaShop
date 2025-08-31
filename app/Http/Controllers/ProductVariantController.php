<?php
namespace App\Http\Controllers;

use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = ProductVariant::with(['product', 'size', 'crust']);

            if ($request->has('product_id')) {
                $query->where('product_id', $request->product_id);
            }

            $variants = $query->get();
            return response()->json($variants);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch product variants',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'size_id' => 'nullable|exists:sizes,id',
                'crust_id' => 'nullable|exists:crusts,id',
                'price' => 'required|numeric|min:0',
                'stock' => 'integer|min:0'
            ]);

            $variant = ProductVariant::create($request->all());
            return response()->json($variant->load(['product', 'size', 'crust']), 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create product variant',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $variant = ProductVariant::with(['product', 'size', 'crust'])->findOrFail($id);
            return response()->json($variant);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch product variant',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $variant = ProductVariant::findOrFail($id);

            $request->validate([
                'price' => 'required|numeric|min:0',
                'stock' => 'integer|min:0'
            ]);

            $variant->update($request->only(['price', 'stock']));
            return response()->json($variant);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update product variant',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $variant = ProductVariant::findOrFail($id);
            $variant->delete();
            return response()->json(['message' => 'Variant deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete product variant',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}