<?php
namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::with(['product', 'user'])->get();
        return response()->json([
            'status' => 'success',
            'data' => $reviews
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'user_id' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $review = Review::create($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $review->load(['product', 'user'])
        ], 201);
    }

    public function show(Review $review)
    {
        return response()->json([
            'status' => 'success',
            'data' => $review->load(['product', 'user'])
        ], 200);
    }

    public function update(Request $request, Review $review)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'user_id' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $review->update($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $review->load(['product', 'user'])
        ], 200);
    }

    public function destroy(Review $review)
    {
        $review->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Review deleted successfully'
        ], 200);
    }
}