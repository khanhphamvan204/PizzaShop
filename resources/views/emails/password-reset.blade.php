<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - Pizza Shop</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
        }

        .content {
            padding: 40px 30px;
        }

        .content h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .content p {
            color: #666;
            margin-bottom: 15px;
        }

        .reset-button {
            display: inline-block;
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }

        .reset-button:hover {
            background: linear-gradient(135deg, #e55a2b, #e8841a);
        }

        .token-info {
            background: #f8f9fa;
            border-left: 4px solid #ff6b35;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 5px 5px 0;
        }

        .footer {
            background: #333;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 14px;
        }

        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🍕 Pizza Shop</h1>
            <p>Đặt lại mật khẩu</p>
        </div>

        <div class="content">
            <h2>Xin chào {{ $user->full_name ?: $user->username }}!</h2>

            <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn tại Pizza Shop.</p>

            <p>Để đặt lại mật khẩu, vui lòng nhấp vào nút bên dưới:</p>

            <div style="text-align: center;">
                <a href="{{ $resetUrl }}" class="reset-button">Đặt lại mật khẩu</a>
            </div>

            <div class="token-info">
                <strong>Thông tin quan trọng:</strong>
                <ul>
                    <li>Link này chỉ có hiệu lực trong <strong>15 phút</strong></li>
                    <li>Link chỉ có thể sử dụng <strong>một lần duy nhất</strong></li>
                    <li>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này</li>
                </ul>
            </div>

            <div class="warning">
                <strong>⚠️ Lưu ý bảo mật:</strong><br>
                Nếu bạn không thể nhấp vào nút trên, hãy sao chép và dán link sau vào trình duyệt:<br>
                <code>{{ $resetUrl }}</code>
            </div>

            <p>Nếu bạn gặp khó khăn hoặc cần hỗ trợ, vui lòng liên hệ với chúng tôi qua:</p>
            <ul>
                <li>📞 Hotline: 1900-1234</li>
                <li>📧 Email: support@pizzashop.com</li>
            </ul>
        </div>

        <div class="footer">
            <p>&copy; 2025 Pizza Shop. Tất cả quyền được bảo lưu.</p>
            <p>Email này được gửi tự động, vui lòng không reply.</p>
        </div>
    </div>
</body>

</html>