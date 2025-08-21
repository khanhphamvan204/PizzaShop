<?php
namespace App\Http\Controllers;

use App\Models\Crust;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CrustController extends Controller
{
    public function index()
    {
        $crusts = Crust::with('variants')->get();
        return response()->json([
            'status' => 'success',
            'data' => $crusts
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $crust = Crust::create($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $crust->load('variants')
        ], 201);
    }

    public function show(Crust $crust)
    {
        return response()->json([
            'status' => 'success',
            'data' => $crust->load('variants')
        ], 200);
    }

    public function update(Request $request, Crust $crust)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $crust->update($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $crust->load('variants')
        ], 200);
    }

    public function destroy(Crust $crust)
    {
        $crust->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Crust deleted successfully'
        ], 200);
    }
}