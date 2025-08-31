<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $credentials = $request->only('email', 'password');

            if (!$token = Auth::guard('api')->attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid credentials',
                    'message' => 'Email or password is incorrect'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => Auth::guard('api')->user()
            ], 200);

        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'messages' => $ve->errors()
            ], 422);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'error' => 'JWT Error',
                'message' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Unexpected error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function me()
    {
        try {
            $user = Auth::guard('api')->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized',
                    'message' => 'No valid token or user not found'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'user' => $user
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Token parsing failed',
                'message' => $e->getMessage()
            ], 401);
        }
    }

    public function logout()
    {
        try {
            Auth::guard('api')->logout();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ], 200);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'error' => 'JWT Error',
                'message' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Unexpected error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
