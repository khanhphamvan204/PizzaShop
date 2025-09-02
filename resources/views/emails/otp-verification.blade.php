<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Mã xác thực email</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f6f6f6; padding: 20px;">
    <table align="center" width="600" cellpadding="0" cellspacing="0" 
           style="background: #ffffff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
        <tr>
            <td align="center" style="padding-bottom: 20px;">
                <h2 style="color: #333;">Xin chào!</h2>
                <p style="color: #555;">Bạn vừa yêu cầu xác thực email cho ứng dụng <b>{{ config('app.name') }}</b>.</p>
            </td>
        </tr>
        <tr>
            <td align="center" style="padding: 30px 0;">
                <div style="font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #2d89ef;">
                    {{ $otp }}
                </div>
                <p style="margin-top: 10px; color: #777;">Mã OTP này sẽ hết hạn sau <b>10 phút</b>.</p>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 20px; color: #666; font-size: 14px;">
                Nếu bạn không yêu cầu hành động này, vui lòng bỏ qua email này.
            </td>
        </tr>
        <tr>
            <td align="center" style="padding-top: 30px; font-size: 12px; color: #aaa;">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </td>
        </tr>
    </table>
</body>
</html>
