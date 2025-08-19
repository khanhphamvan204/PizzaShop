<?php

namespace App\Http\Controllers;

use App\Models\Crust;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CrustController extends Controller
{
    public function index()
    {
        $crusts = Crust::latest()->get();
        return response()->json($crusts);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:50',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $crust = Crust::create($request->all());
        return response()->json($crust, 201);
    }

    public function show($id)
    {
        $crust = Crust::findOrFail($id);
        return response()->json($crust);
    }

    public function update(Request $request, $id)
    {
        $crust = Crust::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:50',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $crust->update($request->all());
        return response()->json($crust);
    }

    public function destroy($id)
    {
        $crust = Crust::findOrFail($id);
        
        // Check if crust is used in any product variants
        if ($crust->productVariants()->count() > 0) {
            return response()->json(['error' => 'Cannot delete crust that is used in product variants'], 422);
        }

        $crust->delete();
        return response()->json(['message' => 'Crust deleted successfully']);
    }
}