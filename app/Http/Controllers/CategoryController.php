<?php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            $categories = Category::withCount('products')->get();
            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            Log::error("Category index error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch categories'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:100',
                'description' => 'nullable|string'
            ]);

            $category = Category::create($request->only(['name', 'description']));
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error("Category store error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create category'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $category = Category::with('products')->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $category
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Category not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error("Category show error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch category'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:100',
                'description' => 'nullable|string'
            ]);

            $category = Category::findOrFail($id);
            $category->update($request->only(['name', 'description']));

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => $category
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Category not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error("Category update error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update category'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Category not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error("Category destroy error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete category'
            ], 500);
        }
    }
}
