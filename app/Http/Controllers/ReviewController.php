<?php
namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Display a paginated list of reviews publicly
     */
    public function index(Request $request)
    {
        try {
            $query = Review::with(['user', 'product', 'combo']);

            // Filter by product_id or combo_id if provided
            if ($request->has('product_id')) {
                $query->where('product_id', $request->product_id);
            }

            if ($request->has('combo_id')) {
                $query->where('combo_id', $request->combo_id);
            }

            if ($request->has('rating')) {
                $query->where('rating', $request->rating);
            }

            // Paginate with 10 reviews per page
            $perPage = $request->input('per_page', 10);
            $reviews = $query->orderBy('created_at', 'desc')->get();

            return response()->json($reviews);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch reviews',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new review
     */
    public function store(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json(['error' => 'Authentication required to create a review'], 401);
            }

            $request->validate([
                'product_id' => 'nullable|exists:products,id',
                'combo_id' => 'nullable|exists:combos,id',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000'
            ]);

            if (!$request->product_id && !$request->combo_id) {
                return response()->json(['error' => 'Either product_id or combo_id is required'], 400);
            }

            if ($request->product_id && $request->combo_id) {
                return response()->json(['error' => 'Cannot review both product and combo'], 400);
            }

            // Check if the user has purchased and received the product/combo
            $userId = Auth::id();
            $orderExists = false;

            if ($request->product_id) {
                $orderExists = OrderItem::whereHas('order', function ($query) use ($userId) {
                    $query->where('user_id', $userId)->where('status', 'delivered');
                })->whereIn('product_variant_id', function ($query) use ($request) {
                    $query->select('id')->from('product_variants')->where('product_id', $request->product_id);
                })->exists();
            } elseif ($request->combo_id) {
                $orderExists = OrderItem::whereHas('order', function ($query) use ($userId) {
                    $query->where('user_id', $userId)->where('status', 'delivered');
                })->where('combo_id', $request->combo_id)->exists();
            }

            if (!$orderExists) {
                return response()->json(['error' => 'You must have purchased and received the product or combo to leave a review'], 403);
            }

            // Check if the user has already reviewed this item
            $existingReview = Review::where('user_id', $userId)
                ->where('product_id', $request->product_id)
                ->where('combo_id', $request->combo_id)
                ->first();

            if ($existingReview) {
                return response()->json(['error' => 'You have already reviewed this item'], 400);
            }

            $review = Review::create([
                'product_id' => $request->product_id,
                'combo_id' => $request->combo_id,
                'user_id' => $userId,
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

    /**
     * Display a specific review
     */
    public function show($id)
    {
        try {
            $review = Review::with(['user', 'product', 'combo'])->findOrFail($id);

            return response()->json($review);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch review',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a review
     */
    public function update(Request $request, $id)
    {
        try {
            $review = Review::findOrFail($id);

            if (!Auth::check() || (Auth::user()->role !== 'admin' && $review->user_id !== Auth::id())) {
                return response()->json(['error' => 'Unauthorized to update this review'], 403);
            }

            $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000'
            ]);

            // Check if the user has purchased and received the product/combo
            $userId = Auth::id();
            $orderExists = false;

            if ($review->product_id) {
                $orderExists = OrderItem::whereHas('order', function ($query) use ($userId) {
                    $query->where('user_id', $userId)->where('status', 'delivered');
                })->whereIn('product_variant_id', function ($query) use ($review) {
                    $query->select('id')->from('product_variants')->where('product_id', $review->product_id);
                })->exists();
            } elseif ($review->combo_id) {
                $orderExists = OrderItem::whereHas('order', function ($query) use ($userId) {
                    $query->where('user_id', $userId)->where('status', 'delivered');
                })->where('combo_id', $review->combo_id)->exists();
            }

            if (!$orderExists) {
                return response()->json(['error' => 'You must have purchased and received the product or combo to update this review'], 403);
            }

            $review->update($request->only(['rating', 'comment']));
            return response()->json($review);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update review',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a review
     */
    public function destroy($id)
    {
        try {
            $review = Review::findOrFail($id);

            if (!Auth::check() || (Auth::user()->role !== 'admin' && $review->user_id !== Auth::id())) {
                return response()->json(['error' => 'Unauthorized to delete this review'], 403);
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