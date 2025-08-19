<?php

namespace App\Http\Controllers;

use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SizeController extends Controller
{
    public function index()
    {
        $sizes = Size::latest()->get();
        return response()->json($sizes);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:50',
            'diameter' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $size = Size::create($request->all());
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

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:50',
            'diameter' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $size->update($request->all());
        return response()->json($size);
    }

    public function destroy($id)
    {
        $size = Size::findOrFail($id);
        
        // Check if size is used in any product variants
        if ($size->productVariants()->count() > 0) {
            return response()->json(['error' => 'Cannot delete size that is used in product variants'], 422);
        }

        $size->delete();
        return response()->json(['message' => 'Size deleted successfully']);
    }
}
