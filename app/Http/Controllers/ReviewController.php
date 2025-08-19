<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::with(['product', 'user'])->latest()->paginate(10);
        return response()->json($reviews);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'user_id' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if user already reviewed this product
        $existingReview = Review::where([
            'product_id' => $request->product_id,
            'user_id' => $request->user_id
        ])->first();

        if ($existingReview) {
            return response()->json(['error' => 'You have already reviewed this product'], 422);
        }

        $review = Review::create($request->all());
        return response()->json($review->load(['product', 'user']), 201);
    }

    public function show($id)
    {
        $review = Review::with(['product', 'user'])->findOrFail($id);
        return response()->json($review);
    }

    public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $review->update($request->only(['rating', 'comment']));
        return response()->json($review);
    }

    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();
        return response()->json(['message' => 'Review deleted successfully']);
    }

    public function getProductReviews($productId)
    {
        $reviews = Review::where('product_id', $productId)
                        ->with('user')
                        ->latest()
                        ->get();
        return response()->json($reviews);
    }

    public function getUserReviews($userId)
    {
        $reviews = Review::where('user_id', $userId)
                        ->with('product')
                        ->latest()
                        ->get();
        return response()->json($reviews);
    }
}