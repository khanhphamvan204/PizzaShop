<?php
// 13. BannerController.php
namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Banner::query();

            if ($request->has('active')) {
                $query->where('active', $request->boolean('active'));
            }

            if ($request->has('position')) {
                $query->where('position', $request->position);
            }

            $banners = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $banners
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch banners',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'image_url' => 'required|string|max:255',
                'link' => 'nullable|string|max:255',
                'position' => 'required|in:homepage_top,homepage_bottom,product_page',
                'active' => 'boolean'
            ]);

            $banner = Banner::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $banner
            ], 201);

        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'messages' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to create banner',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $banner = Banner::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $banner
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Banner not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $banner = Banner::findOrFail($id);

            $request->validate([
                'image_url' => 'required|string|max:255',
                'link' => 'nullable|string|max:255',
                'position' => 'required|in:homepage_top,homepage_bottom,product_page',
                'active' => 'boolean'
            ]);

            $banner->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $banner
            ], 200);

        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'messages' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to update banner',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $banner = Banner::findOrFail($id);
            $banner->delete();

            return response()->json([
                'success' => true,
                'message' => 'Banner deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete banner',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getByPosition($position)
    {
        try {
            $banners = Banner::where('position', $position)
                ->where('active', true)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $banners
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch banners by position',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
