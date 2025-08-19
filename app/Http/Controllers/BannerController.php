<?php
namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::all();
        return response()->json([
            'status' => 'success',
            'data' => $banners
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image_url' => 'required|string|max:255',
            'link' => 'nullable|string|max:255',
            'position' => 'required|in:homepage_top,homepage_bottom,product_page',
            'active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $banner = Banner::create($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $banner
        ], 201);
    }

    public function show(Banner $banner)
    {
        return response()->json([
            'status' => 'success',
            'data' => $banner
        ], 200);
    }

    public function update(Request $request, Banner $banner)
    {
        $validator = Validator::make($request->all(), [
            'image_url' => 'required|string|max:255',
            'link' => 'nullable|string|max:255',
            'position' => 'required|in:homepage_top,homepage_bottom,product_page',
            'active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $banner->update($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $banner
        ], 200);
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Banner deleted successfully'
        ], 200);
    }
}