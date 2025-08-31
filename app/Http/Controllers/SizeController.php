<?php
// 5. SizeController.php
namespace App\Http\Controllers;

use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SizeController extends Controller
{
    public function index()
    {
        try {
            $sizes = Size::all();
            return response()->json($sizes);
        } catch (\Exception $e) {
            Log::error('Failed to fetch sizes', ['message' => $e->getMessage()]);
            return response()->json([
                'error' => 'Failed to fetch sizes',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:50',
                'diameter' => 'nullable|numeric|min:0'
            ]);

            $size = Size::create($request->only(['name', 'diameter']));
            return response()->json($size, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to create size', ['message' => $e->getMessage()]);
            return response()->json([
                'error' => 'Failed to create size',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $size = Size::findOrFail($id);
            return response()->json($size);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Size not found',
                'id' => $id
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to fetch size', ['id' => $id, 'message' => $e->getMessage()]);
            return response()->json([
                'error' => 'Failed to fetch size',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $size = Size::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:50',
                'diameter' => 'nullable|numeric|min:0'
            ]);

            $size->update($request->only(['name', 'diameter']));
            return response()->json($size);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Size not found',
                'id' => $id
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update size', ['id' => $id, 'message' => $e->getMessage()]);
            return response()->json([
                'error' => 'Failed to update size',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $size = Size::findOrFail($id);
            $size->delete();
            return response()->json(['message' => 'Size deleted successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Size not found',
                'id' => $id
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to delete size', ['id' => $id, 'message' => $e->getMessage()]);
            return response()->json([
                'error' => 'Failed to delete size',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
