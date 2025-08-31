<?php
// 6. CrustController.php
namespace App\Http\Controllers;

use App\Models\Crust;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;

class CrustController extends Controller
{
    public function index()
    {
        try {
            $crusts = Crust::all();
            return response()->json($crusts);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch crusts',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:50',
                'description' => 'nullable|string'
            ]);

            $crust = Crust::create($request->only(['name', 'description']));
            return response()->json($crust, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to create crust',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $crust = Crust::findOrFail($id);
            return response()->json($crust);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Crust not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch crust',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:50',
                'description' => 'nullable|string'
            ]);

            $crust = Crust::findOrFail($id);
            $crust->update($request->only(['name', 'description']));
            return response()->json($crust);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Crust not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to update crust',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $crust = Crust::findOrFail($id);
            $crust->delete();
            return response()->json(['message' => 'Crust deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Crust not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to delete crust',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
