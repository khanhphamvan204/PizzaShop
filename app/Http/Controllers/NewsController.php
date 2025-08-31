<?php
// 14. NewsController.php
namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = News::query();

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            }

            $news = $query->orderBy('created_at', 'desc')->get();
            return response()->json($news);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch news',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:200',
                'content' => 'required|string',
                'image_url' => 'nullable|string|max:255'
            ]);

            $news = News::create($request->only(['title', 'content', 'image_url']));
            return response()->json($news, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to create news',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $news = News::findOrFail($id);
            return response()->json($news);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'News not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch news',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:200',
                'content' => 'required|string',
                'image_url' => 'nullable|string|max:255'
            ]);

            $news = News::findOrFail($id);
            $news->update($request->only(['title', 'content', 'image_url']));
            return response()->json($news);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'News not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to update news',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $news = News::findOrFail($id);
            $news->delete();
            return response()->json(['message' => 'News deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'News not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to delete news',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function latest($count = 5)
    {
        try {
            $news = News::orderBy('created_at', 'desc')
                ->limit($count)
                ->get(['id', 'title', 'image_url', 'created_at']);

            return response()->json($news);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch latest news',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
