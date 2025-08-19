<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductVariantController extends Controller
{
    public function index()
    {
        $variants = ProductVariant::with(['product', 'size', 'crust'])->get();
        return response()->json($variants);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'size_id' => 'required|exists:sizes,id',
            'crust_id' => 'required|exists:crusts,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check unique combination
        $exists = ProductVariant::where([
            'product_id' => $request->product_id,
            'size_id' => $request->size_id,
            'crust_id' => $request->crust_id
        ])->exists();

        if ($exists) {
            return response()->json(['error' => 'This variant already exists'], 422);
        }

        $variant = ProductVariant::create($request->all());
        return response()->json($variant->load(['product', 'size', 'crust']), 201);
    }

    public function show($id)
    {
        $variant = ProductVariant::with(['product', 'size', 'crust'])->findOrFail($id);
        return response()->json($variant);
    }

    public function update(Request $request, $id)
    {
        $variant = ProductVariant::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'size_id' => 'required|exists:sizes,id',
            'crust_id' => 'required|exists:crusts,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $variant->update($request->all());
        return response()->json($variant->load(['product', 'size', 'crust']));
    }

    public function destroy($id)
    {
        $variant = ProductVariant::findOrFail($id);
        $variant->delete();
        return response()->json(['message' => 'Product variant deleted successfully']);
    }

    public function getByProduct($productId)
    {
        $variants = ProductVariant::where('product_id', $productId)
                                ->with(['size', 'crust'])
                                ->get();
        return response()->json($variants);
    }
}