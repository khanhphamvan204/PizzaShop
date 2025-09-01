<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $success ? 'Xác thực thành công' : 'Xác thực thất bại' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 60px 40px;
            text-align: center;
            max-width: 500px;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background:
                {{ $success ? 'linear-gradient(90deg, #10b981, #059669)' : 'linear-gradient(90deg, #ef4444, #dc2626)' }}
            ;
        }

        .icon {
            font-size: 80px;
            margin-bottom: 30px;
            animation: bounceIn 1s ease-out;
        }

        .success-icon {
            color: #10b981;
        }

        .error-icon {
            color: #ef4444;
        }

        .title {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 20px;
            color:
                {{ $success ? '#065f46' : '#991b1b' }}
            ;
        }

        .message {
            font-size: 18px;
            line-height: 1.8;
            margin-bottom: 40px;
            color: #6b7280;
        }

        .email-info {
            background-color: #f9fafb;
            border-radius: 12px;
            padding: 20px;
            margin: 30px 0;
            border: 1px solid #e5e7eb;
        }

        .email-info p {
            margin: 0;
            font-size: 16px;
            color: #374151;
        }

        .email-info strong {
            color: #1f2937;
        }

        .actions {
            margin-top: 40px;
        }

        .btn {
            display: inline-block;
            padding: 15px 30px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            margin: 0 10px;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background-color: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background-color: #e5e7eb;
        }

        .footer {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            color: #9ca3af;
        }

        .footer a {
            color: #667eea;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }

            60% {
                transform: scale(1.1);
                opacity: 1;
            }

            100% {
                transform: scale(1);
            }
        }

        @media (max-width: 600px) {
            .container {
                padding: 40px 20px;
                margin: 10px;
            }

            .title {
                font-size: 24px;
            }

            .message {
                font-size: 16px;
            }

            .icon {
                font-size: 60px;
            }

            .btn {
                display: block;
                margin: 10px 0;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        @if($success)
            <div class="icon success-icon">✅</div>
            <h1 class="title">Xác thực thành công!</h1>
            <div class="message">
                {{ $message }}
            </div>

            @if(isset($email))
                <div class="email-info">
                    <p><strong>Email đã xác thực:</strong> {{ $email }}</p>
                    <p style="margin-top: 10px; font-size: 14px; color: #6b7280;">
                        Bạn có thể đăng nhập vào hệ thống ngay bây giờ!
                    </p>
                </div>
            @endif

            <div class="actions">
                <a href="/login" class="btn btn-primary">
                    🚀 Đăng nhập ngay
                </a>
                <a href="/" class="btn btn-secondary">
                    🏠 Trang chủ
                </a>
            </div>
        @else
            <div class="icon error-icon">❌</div>
            <h1 class="title">Xác thực thất bại!</h1>
            <div class="message">
                {{ $message }}
            </div>

            @if(isset($email))
                <div class="email-info">
                    <p><strong>Email:</strong> {{ $email }}</p>
                    <p style="margin-top: 10px; font-size: 14px; color: #6b7280;">
                        Vui lòng thử lại hoặc yêu cầu gửi lại email xác thực.
                    </p>
                </div>
            @endif

            <div class="actions">
                <button onclick="resendVerification()" class="btn btn-primary">
                    📧 Gửi lại email xác thực
                </button>
                <a href="/register" class="btn btn-secondary">
                    👤 Đăng ký lại
                </a>
            </div>
        @endif

        <div class="footer">
            <p>© {{ date('Y') }} Your App Name. All rights reserved.</p>
            <p>
                <a href="/privacy">Chính sách bảo mật</a> |
                <a href="/terms">Điều khoản sử dụng</a>
            </p>
        </div>
    </div>

    <script>
        async function resendVerification() {
            const email = '{{ $email ?? "" }}';

            if (!email) {
                alert('Không tìm thấy thông tin email');
                return;
            }

            try {
                // Disable button
                const btn = event.target;
                btn.disabled = true;
                btn.innerHTML = '⏳ Đang gửi...';

                const response = await fetch('/api/email-verification/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ email: email })
                });

                const data = await response.json();

                if (response.ok) {
                    alert('✅ ' + data.message);
                    btn.innerHTML = '📧 Đã gửi email xác thực';
                } else {
                    alert('❌ ' + data.error);
                    btn.disabled = false;
                    btn.innerHTML = '📧 Gửi lại email xác thực';
                }
            } catch (error) {
                alert('❌ Có lỗi xảy ra khi gửi email');
                btn.disabled = false;
                btn.innerHTML = '📧 Gửi lại email xác thực';
            }
        }
    </script>
</body>

</html>