<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Support\Facades\Log;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        try {
            $token = JWTAuth::getToken(); // Lấy token từ request
            if (!$token) {
                Log::warning('No token provided.');
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }

            $user = JWTAuth::parseToken()->authenticate();
            $userRole = $user->role;
            Log::info('User Role: ' . $userRole);

            if (!in_array($userRole, $roles)) {
                return response()->json(['error' => 'Permission Denied. You do not have the required role.'], 403);
            }

            return $next($request);
        } catch (TokenExpiredException $e) {
            Log::warning('Token expired: ' . $e->getMessage());
            return response()->json(['error' => 'Token expired. Please login again.'], 401);
        } catch (TokenInvalidException $e) {
            Log::warning('Token invalid: ' . $e->getMessage());
            return response()->json(['error' => 'Token invalid.'], 401);
        } catch (JWTException $e) {
            Log::error('JWT Exception: ' . $e->getMessage());
            return response()->json(['error' => 'Unauthorized.'], 401);
        } catch (\Exception $e) {
            Log::error('Unexpected Exception: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong.'], 500);
        }
    }

}