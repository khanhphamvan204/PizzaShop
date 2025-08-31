<?php
// 12. ReviewController.php
namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Review::with(['user', 'product', 'combo']);

            // Chỉ user bình thường mới filter theo user_id
            if (Auth::check() && Auth::user()->role !== 'admin') {
                $query->where('user_id', Auth::id());
            }

            if ($request->has('product_id')) {
                $query->where('product_id', $request->product_id);
            }

            if ($request->has('combo_id')) {
                $query->where('combo_id', $request->combo_id);
            }

            if ($request->has('rating')) {
                $query->where('rating', $request->rating);
            }

            $reviews = $query->orderBy('created_at', 'desc')->get();
            return response()->json($reviews);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch reviews',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json(['error' => 'Authentication required'], 401);
            }

            $request->validate([
                'product_id' => 'nullable|exists:products,id',
                'combo_id' => 'nullable|exists:combos,id',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string'
            ]);

            if (!$request->product_id && !$request->combo_id) {
                return response()->json(['error' => 'Either product_id or combo_id is required'], 400);
            }

            if ($request->product_id && $request->combo_id) {
                return response()->json(['error' => 'Cannot review both product and combo'], 400);
            }

            // Kiểm tra user đã review chưa
            $existingReview = Review::where('user_id', Auth::id())
                ->where('product_id', $request->product_id)
                ->where('combo_id', $request->combo_id)
                ->first();

            if ($existingReview) {
                return response()->json(['error' => 'You have already reviewed this item'], 400);
            }

            $review = Review::create([
                'product_id' => $request->product_id,
                'combo_id' => $request->combo_id,
                'user_id' => Auth::id(),
                'rating' => $request->rating,
                'comment' => $request->comment
            ]);

            return response()->json($review->load(['user', 'product', 'combo']), 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create review',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $review = Review::with(['user', 'product', 'combo'])->findOrFail($id);

            if (Auth::check() && Auth::user()->role !== 'admin' && $review->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            return response()->json($review);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch review',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $review = Review::findOrFail($id);

            if (!Auth::check() || (Auth::user()->role !== 'admin' && $review->user_id !== Auth::id())) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string'
            ]);

            $review->update($request->only(['rating', 'comment']));
            return response()->json($review);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update review',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $review = Review::findOrFail($id);

            if (!Auth::check() || (Auth::user()->role !== 'admin' && $review->user_id !== Auth::id())) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $review->delete();
            return response()->json(['message' => 'Review deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete review',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
