<?php
// 13. BannerController.php
namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $query = Banner::query();

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        if ($request->has('position')) {
            $query->where('position', $request->position);
        }

        $banners = $query->orderBy('created_at', 'desc')->get();
        return response()->json($banners);
    }

    public function store(Request $request)
    {
        $request->validate([
            'image_url' => 'required|string|max:255',
            'link' => 'nullable|string|max:255',
            'position' => 'required|in:homepage_top,homepage_bottom,product_page',
            'active' => 'boolean'
        ]);

        $banner = Banner::create($request->all());
        return response()->json($banner, 201);
    }

    public function show($id)
    {
        $banner = Banner::findOrFail($id);
        return response()->json($banner);
    }

    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $request->validate([
            'image_url' => 'required|string|max:255',
            'link' => 'nullable|string|max:255',
            'position' => 'required|in:homepage_top,homepage_bottom,product_page',
            'active' => 'boolean'
        ]);

        $banner->update($request->all());
        return response()->json($banner);
    }

    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->delete();
        return response()->json(['message' => 'Banner deleted successfully']);
    }

    public function getByPosition($position)
    {
        $banners = Banner::where('position', $position)
            ->where('active', true)
            ->get();
        return response()->json($banners);
    }
}