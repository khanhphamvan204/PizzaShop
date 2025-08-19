<?php
namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::all();
        return response()->json([
            'status' => 'success',
            'data' => $news
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'content' => 'required|string',
            'image_url' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $news = News::create($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $news
        ], 201);
    }

    public function show(News $news)
    {
        return response()->json([
            'status' => 'success',
            'data' => $news
        ], 200);
    }

    public function update(Request $request, News $news)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'content' => 'required|string',
            'image_url' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $news->update($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $news
        ], 200);
    }

    public function destroy(News $news)
    {
        $news->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'News deleted successfully'
        ], 200);
    }
}