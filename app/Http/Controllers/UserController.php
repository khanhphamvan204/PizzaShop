<?php

// 1. UserController.php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15);
        return response()->json($users);
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:users',
            'password' => 'required|string|min:6',
            'email' => 'required|email|unique:users',
            'full_name' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'role' => 'in:customer,admin'
        ]);

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'email' => $request->email,
            'full_name' => $request->full_name,
            'address' => $request->address,
            'phone' => $request->phone,
            'role' => $request->role ?? 'customer'
        ]);

        return response()->json($user, 201);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'full_name' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'role' => 'in:customer,admin'
        ]);

        $updateData = $request->only(['username', 'email', 'full_name', 'address', 'phone', 'role']);

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:6']);
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);
        return response()->json($user);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function profile()
    {
        return response()->json(Auth::user());
    }

    // public function updateProfile(Request $request)
    // {
    //     $user = Auth::user();

    //     $request->validate([
    //         'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
    //         'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
    //         'full_name' => 'nullable|string|max:100',
    //         'address' => 'nullable|string',
    //         'phone' => 'nullable|string|max:20'
    //     ]);

    //     $updateData = $request->only(['username', 'email', 'full_name', 'address', 'phone']);

    //     if ($request->filled('password')) {
    //         $request->validate(['password' => 'string|min:6']);
    //         $updateData['password'] = Hash::make($request->password);
    //     }

    //     $user->update($updateData);
    //     return response()->json($user);
    // }
}