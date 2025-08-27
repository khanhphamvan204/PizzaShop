<?php
// 6. CrustController.php
namespace App\Http\Controllers;

use App\Models\Crust;
use Illuminate\Http\Request;

class CrustController extends Controller
{
    public function index()
    {
        $crusts = Crust::all();
        return response()->json($crusts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'nullable|string'
        ]);

        $crust = Crust::create($request->only(['name', 'description']));
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

        $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'nullable|string'
        ]);

        $crust->update($request->only(['name', 'description']));
        return response()->json($crust);
    }

    public function destroy($id)
    {
        $crust = Crust::findOrFail($id);
        $crust->delete();
        return response()->json(['message' => 'Crust deleted successfully']);
    }
}