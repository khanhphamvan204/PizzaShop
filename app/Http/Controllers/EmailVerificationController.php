<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Carbon\Carbon;

class EmailVerificationController extends Controller
{
    /**
     * Gửi OTP xác thực email (cho đăng ký tài khoản mới)
     */
    public function sendEmailOTPForRegistration(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Dữ liệu không hợp lệ',
                    'messages' => $validator->errors()
                ], 422);
            }

            $email = $request->email;

            // Kiểm tra email đã được đăng ký chưa
            if (User::where('email', $email)->exists()) {
                return response()->json([
                    'error' => 'Email này đã được đăng ký'
                ], 409);
            }

            // Tạo OTP 6 số
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Lưu OTP vào cache với thời gian hết hạn 10 phút
            $cacheKey = 'email_otp_' . $email;
            $otpData = [
                'otp' => $otp,
                'email' => $email,
                'created_at' => now(),
                'attempts' => 0,
                'purpose' => 'registration' // Mục đích: đăng ký
            ];

            Cache::put($cacheKey, $otpData, now()->addMinutes(10));

            // Gửi email OTP
            Mail::send('emails.otp-verification', ['otp' => $otp, 'email' => $email], function ($message) use ($email) {
                $message->to($email)
                    ->subject('Mã xác thực email đăng ký - ' . config('app.name'));
            });

            return response()->json([
                'message' => 'Mã OTP đã được gửi đến email của bạn để xác thực đăng ký',
                'expires_in' => '10 phút'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Có lỗi xảy ra khi gửi OTP',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gửi OTP xác thực email tồn tại (không kiểm tra User)
     */
    public function sendEmailOTP(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Dữ liệu không hợp lệ',
                    'messages' => $validator->errors()
                ], 422);
            }

            $email = $request->email;
            $cacheKey = 'email_otp_' . $email;
            $resendKey = 'otp_resend_' . $email;

            // Kiểm tra thời gian chờ giữa các lần gửi (ví dụ 60 giây)
            if (Cache::has($resendKey)) {
                $remainingTime = Cache::get($resendKey) - time();
                if ($remainingTime > 0) {
                    return response()->json([
                        'error' => "Vui lòng chờ {$remainingTime} giây trước khi gửi lại OTP"
                    ], 429);
                }
            }

            // Tạo OTP 6 số
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Lưu OTP vào cache với thời gian hết hạn 10 phút
            $otpData = [
                'otp' => $otp,
                'email' => $email,
                'created_at' => now(),
                'attempts' => 0,
                'purpose' => 'email_verification'
            ];

            Cache::put($cacheKey, $otpData, now()->addMinutes(10));

            // Đặt thời gian chờ 60 giây
            Cache::put($resendKey, time() + 60, now()->addSeconds(60));

            // Gửi email OTP
            Mail::send('emails.otp-verification', ['otp' => $otp, 'email' => $email], function ($message) use ($email) {
                $message->to($email)
                    ->subject('Mã xác thực email - ' . config('app.name'));
            });

            return response()->json([
                'message' => 'Mã OTP đã được gửi đến email của bạn',
                'expires_in' => '10 phút',
                'cooldown' => '60 giây'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Có lỗi xảy ra khi gửi OTP',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Xác thực OTP email
     */
    public function verifyEmailOTP(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'otp' => 'required|digits:6'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Dữ liệu không hợp lệ',
                    'messages' => $validator->errors()
                ], 422);
            }

            $email = $request->email;
            $otp = $request->otp;
            $cacheKey = 'email_otp_' . $email;

            // Kiểm tra OTP có tồn tại trong cache không
            if (!Cache::has($cacheKey)) {
                return response()->json([
                    'error' => 'Mã OTP không tồn tại hoặc đã hết hạn'
                ], 404);
            }

            $otpData = Cache::get($cacheKey);

            // Tăng số lần thử
            $otpData['attempts']++;

            // Kiểm tra số lần thử tối đa (5 lần)
            if ($otpData['attempts'] > 5) {
                Cache::forget($cacheKey);
                return response()->json([
                    'error' => 'Bạn đã nhập sai quá nhiều lần. Vui lòng yêu cầu mã OTP mới'
                ], 429);
            }

            // Cập nhật số lần thử vào cache
            Cache::put($cacheKey, $otpData, now()->addMinutes(10));

            // Kiểm tra OTP có đúng không
            if ($otpData['otp'] !== $otp) {
                return response()->json([
                    'error' => 'Mã OTP không chính xác',
                    'attempts_left' => 5 - $otpData['attempts']
                ], 400);
            }

            // OTP đúng, xóa khỏi cache và tạo token xác thực
            Cache::forget($cacheKey);

            // Tạo token xác thực email (có thể dùng cho bước đăng ký tiếp theo)
            $verificationToken = bin2hex(random_bytes(32));
            $verificationKey = 'email_verified_' . $email;

            Cache::put($verificationKey, [
                'email' => $email,
                'token' => $verificationToken,
                'verified_at' => now()
            ], now()->addHours(1)); // Token có hiệu lực 1 tiếng

            return response()->json([
                'message' => 'Email đã được xác thực thành công',
                'verification_token' => $verificationToken
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Có lỗi xảy ra khi xác thực OTP',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gửi lại OTP
     */
    public function resendEmailOTP(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Email không hợp lệ',
                    'messages' => $validator->errors()
                ], 422);
            }

            $email = $request->email;
            $cacheKey = 'email_otp_' . $email;
            $resendKey = 'otp_resend_' . $email;

            // Kiểm tra thời gian chờ giữa các lần gửi (60 giây)
            if (Cache::has($resendKey)) {
                $remainingTime = Cache::get($resendKey) - time();
                return response()->json([
                    'error' => 'Vui lòng chờ ' . $remainingTime . ' giây trước khi gửi lại OTP'
                ], 429);
            }

            // Xóa OTP cũ nếu có
            if (Cache::has($cacheKey)) {
                Cache::forget($cacheKey);
            }

            // Tạo OTP mới
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $otpData = [
                'otp' => $otp,
                'email' => $email,
                'created_at' => now(),
                'attempts' => 0
            ];

            Cache::put($cacheKey, $otpData, now()->addMinutes(10));

            // Đặt thời gian chờ 60 giây
            Cache::put($resendKey, time() + 60, now()->addSeconds(60));

            // Gửi email OTP
            Mail::send('emails.otp-verification', ['otp' => $otp, 'email' => $email], function ($message) use ($email) {
                $message->to($email)
                    ->subject('Mã xác thực email mới - ' . config('app.name'));
            });

            return response()->json([
                'message' => 'Mã OTP mới đã được gửi đến email của bạn'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Có lỗi xảy ra khi gửi lại OTP',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hủy xác thực email
     */
    public function cancelEmailVerification(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email'
            ]);

            $email = $request->email;
            $cacheKey = 'email_otp_' . $email;
            $verificationKey = 'email_verified_' . $email;

            $cancelled = false;

            // Xóa OTP nếu có
            if (Cache::has($cacheKey)) {
                Cache::forget($cacheKey);
                $cancelled = true;
            }

            // Xóa token xác thực nếu có
            if (Cache::has($verificationKey)) {
                Cache::forget($verificationKey);
                $cancelled = true;
            }

            if ($cancelled) {
                return response()->json([
                    'message' => 'Quá trình xác thực email đã được hủy'
                ]);
            }

            return response()->json([
                'message' => 'Không có quá trình xác thực email nào để hủy'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Có lỗi xảy ra',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kiểm tra trạng thái xác thực email
     */
    public function checkVerificationStatus(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email'
            ]);

            $email = $request->email;
            $verificationKey = 'email_verified_' . $email;

            if (Cache::has($verificationKey)) {
                $data = Cache::get($verificationKey);
                return response()->json([
                    'verified' => true,
                    'verified_at' => $data['verified_at'],
                    'token' => $data['token']
                ]);
            }

            $otpKey = 'email_otp_' . $email;
            if (Cache::has($otpKey)) {
                $otpData = Cache::get($otpKey);
                return response()->json([
                    'verified' => false,
                    'otp_sent' => true,
                    'expires_at' => $otpData['created_at']->addMinutes(10),
                    'attempts' => $otpData['attempts']
                ]);
            }

            return response()->json([
                'verified' => false,
                'otp_sent' => false
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Có lỗi xảy ra',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
