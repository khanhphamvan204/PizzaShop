<?php
// 1. UserController.php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
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
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch users',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
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

        } catch (ValidationException $ve) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create user',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            return response()->json($user);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'User not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
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

        } catch (ValidationException $ve) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update user',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return response()->json(['message' => 'User deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete user',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function profile()
    {
        try {
            return response()->json(Auth::user());
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch profile',
                'message' => $e->getMessage()
            ], 500);
        }
    }



    /**
     * Gửi email reset password
     */
    public function sendPasswordResetEmail(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email'
            ]);

            $email = $request->email;

            // Kiểm tra user có tồn tại không
            $user = User::where('email', $email)->first();
            if (!$user) {
                return response()->json([
                    'error' => 'Email does not exist'
                ], 404);
            }

            // Kiểm tra giới hạn yêu cầu (chống spam)
            $rateLimitKey = 'password_reset_rate_limit_' . $email;
            if (Cache::has($rateLimitKey)) {
                return response()->json([
                    'error' => 'You can only request a password reset once every 5 minutes'
                ], 429);
            }

            // Tạo token ngẫu nhiên
            $token = Str::random(60);

            // Lưu token vào cache với thời gian hết hạn 15 phút
            $cacheKey = 'password_reset_' . $email;
            Cache::put($cacheKey, [
                'token' => $token,
                'email' => $email,
                'user_id' => $user->id,
                'created_at' => Carbon::now(),
                'expires_at' => Carbon::now()->addMinutes(15)
            ], 15 * 60); // 15 phút

            // Đặt rate limit 5 phút
            Cache::put($rateLimitKey, true, 1);

            // Gửi email
            $resetUrl = url('/reset-password?email=' . urlencode($email) . '&token=' . $token);

            Mail::send('emails.password-reset', [
                'user' => $user,
                'resetUrl' => $resetUrl,
                'token' => $token
            ], function ($message) use ($user) {
                $message->to($user->email, $user->full_name ?: $user->username)
                    ->subject('Password Reset Request');
            });

            return response()->json([
                'message' => 'Password reset email sent successfully',
                'expires_in' => '15 phút'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to send password reset email',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xác thực token reset password
     */
    public function verifyResetToken(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'token' => 'required|string'
            ]);

            $email = $request->email;
            $token = $request->token;

            // Lấy thông tin từ cache
            $cacheKey = 'password_reset_' . $email;
            $resetData = Cache::get($cacheKey);

            if (!$resetData) {
                return response()->json([
                    'valid' => false,
                    'error' => 'Token không hợp lệ hoặc đã hết hạn'
                ], 400);
            }

            // Kiểm tra token
            if ($resetData['token'] !== $token) {
                return response()->json([
                    'valid' => false,
                    'error' => 'Token không đúng'
                ], 400);
            }

            // Kiểm tra thời gian hết hạn
            if (Carbon::now()->greaterThan($resetData['expires_at'])) {
                Cache::forget($cacheKey);
                return response()->json([
                    'valid' => false,
                    'error' => 'Token đã hết hạn'
                ], 400);
            }

            // Kiểm tra user vẫn tồn tại
            $user = User::where('email', $email)->first();
            if (!$user) {
                Cache::forget($cacheKey);
                return response()->json([
                    'valid' => false,
                    'error' => 'Tài khoản không tồn tại'
                ], 404);
            }

            return response()->json([
                'valid' => true,
                'email' => $email,
                'username' => $user->username,
                'expires_at' => $resetData['expires_at']->toISOString(),
                'time_remaining' => Carbon::now()->diffInMinutes($resetData['expires_at'], false) . ' phút'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'error' => 'Có lỗi xảy ra khi xác thực token',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset mật khẩu
     */
    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'token' => 'required|string',
                'password' => 'required|string|min:6|confirmed'
            ]);

            $email = $request->email;
            $token = $request->token;

            // Lấy thông tin từ cache
            $cacheKey = 'password_reset_' . $email;
            $resetData = Cache::get($cacheKey);

            if (!$resetData) {
                return response()->json([
                    'error' => 'Token không hợp lệ hoặc đã hết hạn'
                ], 400);
            }

            // Kiểm tra token
            if ($resetData['token'] !== $token) {
                return response()->json([
                    'error' => 'Token không đúng'
                ], 400);
            }

            // Kiểm tra thời gian hết hạn
            if (Carbon::now()->greaterThan($resetData['expires_at'])) {
                Cache::forget($cacheKey);
                return response()->json([
                    'error' => 'Token đã hết hạn'
                ], 400);
            }

            // Tìm user và cập nhật mật khẩu
            $user = User::where('email', $email)->first();
            if (!$user) {
                Cache::forget($cacheKey);
                return response()->json([
                    'error' => 'Không tìm thấy người dùng'
                ], 404);
            }

            // Cập nhật mật khẩu mới
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            // Xóa token khỏi cache (chỉ sử dụng 1 lần)
            Cache::forget($cacheKey);

            // Xóa tất cả session của user (đăng xuất khỏi tất cả thiết bị)
            // Cache::forget('user_sessions_' . $user->id);

            return response()->json([
                'message' => 'Mật khẩu đã được cập nhật thành công. Vui lòng đăng nhập lại với mật khẩu mới.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Có lỗi xảy ra khi reset mật khẩu',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hủy token reset password
     */
    public function cancelPasswordReset(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email'
            ]);

            $email = $request->email;
            $cacheKey = 'password_reset_' . $email;

            if (Cache::has($cacheKey)) {
                Cache::forget($cacheKey);
                return response()->json([
                    'message' => 'Yêu cầu đặt lại mật khẩu đã được hủy'
                ]);
            }

            return response()->json([
                'message' => 'Không có yêu cầu đặt lại mật khẩu nào để hủy'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Có lỗi xảy ra',
                'message' => $e->getMessage()
            ], 500);
        }
    }

}