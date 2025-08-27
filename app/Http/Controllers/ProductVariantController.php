<?php
// 4. ProductVariantController.php
namespace App\Http\Controllers;

use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductVariant::with(['product', 'size', 'crust']);

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $variants = $query->get();
        return response()->json($variants);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'size_id' => 'nullable|exists:sizes,id',
            'crust_id' => 'nullable|exists:crusts,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'integer|min:0'
        ]);

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

        $request->validate([
            'price' => 'required|numeric|min:0',
            'stock' => 'integer|min:0'
        ]);

        $variant->update($request->only(['price', 'stock']));
        return response()->json($variant);
    }

    public function destroy($id)
    {
        $variant = ProductVariant::findOrFail($id);
        $variant->delete();
        return response()->json(['message' => 'Variant deleted successfully']);
    }
}