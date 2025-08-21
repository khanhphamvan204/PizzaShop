<?php
namespace App\Http\Controllers;

use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SizeController extends Controller
{
    public function index()
    {
        $sizes = Size::with('variants')->get();
        return response()->json([
            'status' => 'success',
            'data' => $sizes
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'diameter' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $size = Size::create($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $size->load('variants')
        ], 201);
    }

    public function show(Size $size)
    {
        return response()->json([
            'status' => 'success',
            'data' => $size->load('variants')
        ], 200);
    }

    public function update(Request $request, Size $size)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'diameter' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $size->update($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $size->load('variants')
        ], 200);
    }

    public function destroy(Size $size)
    {
        $size->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Size deleted successfully'
        ], 200);
    }
}