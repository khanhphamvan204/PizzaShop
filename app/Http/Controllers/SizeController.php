<?php
// 5. SizeController.php
namespace App\Http\Controllers;

use App\Models\Size;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    public function index()
    {
        $sizes = Size::all();
        return response()->json($sizes);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'diameter' => 'nullable|numeric|min:0'
        ]);

        $size = Size::create($request->only(['name', 'diameter']));
        return response()->json($size, 201);
    }

    public function show($id)
    {
        $size = Size::findOrFail($id);
        return response()->json($size);
    }

    public function update(Request $request, $id)
    {
        $size = Size::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:50',
            'diameter' => 'nullable|numeric|min:0'
        ]);

        $size->update($request->only(['name', 'diameter']));
        return response()->json($size);
    }

    public function destroy($id)
    {
        $size = Size::findOrFail($id);
        $size->delete();
        return response()->json(['message' => 'Size deleted successfully']);
    }
}