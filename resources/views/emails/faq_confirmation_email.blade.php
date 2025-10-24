<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận câu hỏi từ Pizza Shop</title>
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

        .content p {
            margin: 0 0 20px;
            color: #333;
        }

        .question-box {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 5px;
            background: #f9f9f9;
            border: 1px solid #ddd;
        }

        .question-box h3 {
            margin: 0 0 10px;
            color: #ff6b35;
            font-size: 18px;
        }

        .footer {
            background: #f4f4f4;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }

        .footer a {
            color: #ff6b35;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🍕 Pizza Shop</h1>
            <p>Xác nhận câu hỏi của bạn</p>
        </div>

        <div class="content">
            <p>Kính gửi {{ $faq->name }},</p>
            <p>Cảm ơn bạn đã gửi câu hỏi đến Pizza Shop! Chúng tôi đã nhận được câu hỏi của bạn và sẽ trả lời trong thời
                gian sớm nhất.</p>

            <div class="question-box">
                <h3>Câu hỏi của bạn:</h3>
                <p>{{ $faq->question }}</p>
            </div>

            <p>Vui lòng chờ phản hồi từ đội ngũ của chúng tôi. Nếu bạn có thêm câu hỏi, hãy gửi qua <a
                    href="{{ url('/contact') }}">form liên hệ</a> hoặc gọi hotline.</p>
            <p>Trân trọng,<br>Đội ngũ Pizza Shop</p>
        </div>

        <div class="footer">
            <p>Pizza Shop - Thưởng thức pizza ngon, mọi lúc, mọi nơi!</p>
            <p><a href="{{ url('/') }}">Truy cập website</a> | <a href="{{ url('/contact') }}">Liên hệ hỗ trợ</a></p>
        </div>
    </div>
</body>

</html>