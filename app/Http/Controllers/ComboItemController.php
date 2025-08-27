<?php
// 19. ComboItemController.php
namespace App\Http\Controllers;

use App\Models\ComboItem;
use App\Models\Combo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComboItemController extends Controller
{
    public function index(Request $request)
    {
        $query = ComboItem::with(['combo', 'productVariant.product', 'productVariant.size', 'productVariant.crust']);

        if ($request->has('combo_id')) {
            $query->where('combo_id', $request->combo_id);
        }

        $comboItems = $query->get();
        return response()->json($comboItems);
    }

    public function store(Request $request)
    {
        $request->validate([
            'combo_id' => 'required|exists:combos,id',
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1'
        ]);

        // Kiểm tra xem combo item đã tồn tại chưa
        $existingItem = ComboItem::where('combo_id', $request->combo_id)
            ->where('product_variant_id', $request->product_variant_id)
            ->first();

        if ($existingItem) {
            return response()->json(['error' => 'This product variant is already in the combo'], 400);
        }

        $comboItem = ComboItem::create($request->all());

        return response()->json($comboItem->load(['combo', 'productVariant.product']), 201);
    }

    public function show($id)
    {
        $comboItem = ComboItem::with(['combo', 'productVariant.product', 'productVariant.size', 'productVariant.crust'])
            ->findOrFail($id);
        return response()->json($comboItem);
    }

    public function update(Request $request, $id)
    {
        $comboItem = ComboItem::findOrFail($id);

        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $comboItem->update(['quantity' => $request->quantity]);

        return response()->json($comboItem->load(['combo', 'productVariant.product']));
    }

    public function destroy($id)
    {
        $comboItem = ComboItem::findOrFail($id);
        $comboItem->delete();

        return response()->json(['message' => 'Combo item deleted successfully']);
    }

    public function getByCombo($comboId)
    {
        $combo = Combo::findOrFail($comboId);

        $comboItems = ComboItem::where('combo_id', $comboId)
            ->with(['productVariant.product', 'productVariant.size', 'productVariant.crust'])
            ->get();

        // Tính tổng giá trị của các item trong combo
        $totalItemsPrice = 0;
        foreach ($comboItems as $item) {
            $totalItemsPrice += $item->productVariant->price * $item->quantity;
        }

        return response()->json([
            'combo' => $combo,
            'items' => $comboItems,
            'total_items_price' => $totalItemsPrice,
            'combo_price' => $combo->price,
            'savings' => $totalItemsPrice - $combo->price
        ]);
    }

    public function bulkUpdate(Request $request, $comboId)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        $combo = Combo::findOrFail($comboId);

        DB::beginTransaction();
        try {
            // Xóa tất cả items hiện tại
            ComboItem::where('combo_id', $comboId)->delete();

            // Thêm items mới
            foreach ($request->items as $itemData) {
                ComboItem::create([
                    'combo_id' => $comboId,
                    'product_variant_id' => $itemData['product_variant_id'],
                    'quantity' => $itemData['quantity']
                ]);
            }

            DB::commit();

            $updatedItems = ComboItem::where('combo_id', $comboId)
                ->with(['productVariant.product', 'productVariant.size', 'productVariant.crust'])
                ->get();

            return response()->json([
                'message' => 'Combo items updated successfully',
                'items' => $updatedItems
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to update combo items'], 500);
        }
    }
}