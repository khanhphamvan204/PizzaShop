<?php
// 7. ComboController.php
namespace App\Http\Controllers;

use App\Models\Combo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComboController extends Controller
{
    public function index(Request $request)
    {
        $query = Combo::with(['items.productVariant.product', 'items.productVariant.size', 'items.productVariant.crust']);

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        if ($request->has('current')) {
            $now = now();
            $query->where('start_date', '<=', $now)->where('end_date', '>=', $now);
        }

        $combos = $query->paginate(12);
        return response()->json($combos);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image_url' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_active' => 'boolean',
            'items' => 'required|array',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        DB::beginTransaction();
        try {
            $combo = Combo::create($request->only([
                'name',
                'description',
                'price',
                'image_url',
                'start_date',
                'end_date',
                'is_active'
            ]));

            foreach ($request->items as $itemData) {
                $combo->items()->create($itemData);
            }

            DB::commit();
            return response()->json($combo->load('items'), 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to create combo'], 500);
        }
    }

    public function show($id)
    {
        $combo = Combo::with(['items.productVariant.product', 'items.productVariant.size', 'items.productVariant.crust'])
            ->findOrFail($id);
        return response()->json($combo);
    }

    public function update(Request $request, $id)
    {
        $combo = Combo::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image_url' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_active' => 'boolean'
        ]);

        $combo->update($request->only([
            'name',
            'description',
            'price',
            'image_url',
            'start_date',
            'end_date',
            'is_active'
        ]));

        return response()->json($combo);
    }

    public function destroy($id)
    {
        $combo = Combo::findOrFail($id);
        $combo->delete();
        return response()->json(['message' => 'Combo deleted successfully']);
    }

    public function active()
    {
        $now = now();
        $combos = Combo::where('is_active', true)
            ->where('start_date', '<=', $now)->where('end_date', '>=', $now)
            ->with(['items.productVariant.product'])
            ->get();
        return response()->json($combos);
    }
}