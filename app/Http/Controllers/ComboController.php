<?php
namespace App\Http\Controllers;

use App\Models\Combo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ComboController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Combo::with([
                'items.productVariant.product',
                'items.productVariant.size',
                'items.productVariant.crust'
            ]);

            if ($request->has('active')) {
                $query->where('is_active', $request->boolean('active'));
            }

            if ($request->has('current')) {
                $now = now();
                $query->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now);
            }

            $combos = $query->get();

            return response()->json([
                'success' => true,
                'data' => $combos
            ]);
        } catch (\Exception $e) {
            Log::error("Combo index error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch combos'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
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

            return response()->json([
                'success' => true,
                'message' => 'Combo created successfully',
                'data' => $combo->load('items')
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Combo store error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create combo'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $combo = Combo::with([
                'items.productVariant.product',
                'items.productVariant.size',
                'items.productVariant.crust'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $combo
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Combo not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error("Combo show error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch combo'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'image_url' => 'nullable|string|max:255',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after:start_date',
                'is_active' => 'boolean'
            ]);

            $combo = Combo::findOrFail($id);
            $combo->update($request->only([
                'name',
                'description',
                'price',
                'image_url',
                'start_date',
                'end_date',
                'is_active'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Combo updated successfully',
                'data' => $combo
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Combo not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error("Combo update error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update combo'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $combo = Combo::findOrFail($id);
            $combo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Combo deleted successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Combo not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error("Combo destroy error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete combo'
            ], 500);
        }
    }

    public function active()
    {
        try {
            $now = now();
            $combos = Combo::where('is_active', true)
                ->where('start_date', '<=', $now)
                ->where('end_date', '>=', $now)
                ->with(['items.productVariant.product'])
                ->get();

            return response()->json([
                'success' => true,
                'data' => $combos
            ]);
        } catch (\Exception $e) {
            Log::error("Combo active error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch active combos'
            ], 500);
        }
    }
}
