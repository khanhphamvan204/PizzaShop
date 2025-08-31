<?php
namespace App\Http\Controllers;

use App\Models\ComboItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ComboItemController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'combo_id' => 'required|exists:combos,id',
                'product_variant_id' => 'required|exists:product_variants,id',
                'quantity' => 'required|integer|min:1'
            ]);

            $existingItem = ComboItem::where('combo_id', $request->combo_id)
                ->where('product_variant_id', $request->product_variant_id)
                ->first();

            if ($existingItem) {
                return response()->json([
                    'success' => false,
                    'error' => 'This product variant is already in the combo'
                ], 400);
            }

            $comboItem = ComboItem::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Combo item created successfully',
                'data' => $comboItem->load(['combo', 'productVariant.product'])
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error("ComboItem store error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create combo item'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $comboItem = ComboItem::with([
                'combo',
                'productVariant.product',
                'productVariant.size',
                'productVariant.crust'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $comboItem
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Combo item not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error("ComboItem show error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch combo item'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:1'
            ]);

            $comboItem = ComboItem::findOrFail($id);
            $comboItem->update(['quantity' => $request->quantity]);

            return response()->json([
                'success' => true,
                'message' => 'Combo item updated successfully',
                'data' => $comboItem->load(['combo', 'productVariant.product'])
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Combo item not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error("ComboItem update error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update combo item'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $comboItem = ComboItem::findOrFail($id);
            $comboItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Combo item deleted successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Combo item not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error("ComboItem destroy error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete combo item'
            ], 500);
        }
    }
}
