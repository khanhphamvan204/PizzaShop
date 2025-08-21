<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['orders', 'carts', 'reviews', 'contacts'])->get();
        return response()->json([
            'status' => 'success',
            'data' => $users
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:50|unique:users',
            'password' => 'required|string|min:6',
            'email' => 'required|email|max:100|unique:users',
            'full_name' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:customer,admin'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::bcrypt($request->password),
            'email' => $request->email,
            'full_name' => $request->full_name,
            'address' => $request->address,
            'phone' => $request->phone,
            'role' => $request->role
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $user->load(['orders', 'carts', 'reviews', 'contacts'])
        ], 201);
    }

    public function show(User $user)
    {
        return response()->json([
            'status' => 'success',
            'data' => $user->load(['orders', 'carts', 'reviews', 'contacts'])
        ], 200);
    }

    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:6',
            'email' => 'required|email|max:100|unique:users,email,' . $user->id,
            'full_name' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:customer,admin'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        if ($request->has('password') && $request->password) {
            $data['password'] = Hash::bcrypt($request->password);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        return response()->json([
            'status' => 'success',
            'data' => $user->load(['orders', 'carts', 'reviews', 'contacts'])
        ], 200);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully'
        ], 200);
    }
}