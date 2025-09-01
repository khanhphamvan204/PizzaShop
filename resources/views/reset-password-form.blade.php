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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 500px;
            width: 90%;
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

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: #ff6b35;
        }

        .submit-button {
            width: 100%;
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            color: white;
            padding: 15px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-button:hover {
            background: linear-gradient(135deg, #e55a2b, #e8841a);
            transform: translateY(-2px);
        }

        .submit-button:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: none;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .loading {
            display: none;
            text-align: center;
            margin: 20px 0;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #ff6b35;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #ff6b35;
            text-decoration: none;
        }

        .back-link a:hover {
            text-decoration: underline;
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
            <div id="message" class="message"></div>

            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>Đang xử lý...</p>
            </div>

            <form id="resetPasswordForm">
                <input type="hidden" name="email" value="{{ $email }}">
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group">
                    <label for="email_display">Email:</label>
                    <input type="email" id="email_display" value="{{ $email }}" disabled>
                </div>

                <div class="form-group">
                    <label for="password">Mật khẩu mới:</label>
                    <input type="password" id="password" name="password" required minlength="6">
                    <small style="color: #666;">Tối thiểu 6 ký tự</small>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Xác nhận mật khẩu:</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                        minlength="6">
                </div>

                <button type="submit" class="submit-button" id="submitButton">
                    Cập nhật mật khẩu
                </button>
            </form>

            {{-- <div class="back-link">
                <a href="/login">← Quay lại đăng nhập</a>
            </div> --}}
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('resetPasswordForm');
            const message = document.getElementById('message');
            const loading = document.getElementById('loading');
            const submitButton = document.getElementById('submitButton');

            // Verify token khi trang load
            verifyToken();

            function verifyToken() {
                const email = document.querySelector('input[name="email"]').value;
                const token = document.querySelector('input[name="token"]').value;

                console.log('Verifying token for:', email); // Debug log

                fetch('/api/password/verify-token', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email, token })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.valid) {
                            showMessage(data.error || 'Token không hợp lệ', 'error');
                            form.style.display = 'none';
                        } else {
                            showMessage(`Token hợp lệ. Còn lại: ${data.time_remaining}`, 'success');
                        }
                    })
                    .catch(error => {
                        console.error('Verify token error:', error); // Debug log
                        showMessage('Có lỗi xảy ra khi xác thực token', 'error');
                        form.style.display = 'none';
                    });
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const password = document.getElementById('password').value;
                const passwordConfirmation = document.getElementById('password_confirmation').value;

                if (password !== passwordConfirmation) {
                    showMessage('Mật khẩu xác nhận không khớp', 'error');
                    return;
                }

                if (password.length < 6) {
                    showMessage('Mật khẩu phải có ít nhất 6 ký tự', 'error');
                    return;
                }

                submitButton.disabled = true;
                loading.style.display = 'block';
                message.style.display = 'none';

                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());

                fetch('/api/password/reset', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                    .then(response => response.json())
                    .then(data => {
                        loading.style.display = 'none';

                        if (data.error) {
                            showMessage(data.error, 'error');
                            submitButton.disabled = false;
                        } else {
                            showMessage(data.message || 'Mật khẩu đã được cập nhật thành công!', 'success');
                            form.style.display = 'none';
                            // setTimeout(() => {
                            //     window.location.href = '/login';
                            // }, 3000);
                        }
                    })
                    .catch(error => {
                        loading.style.display = 'none';
                        showMessage('Có lỗi xảy ra khi cập nhật mật khẩu', 'error');
                        submitButton.disabled = false;
                    });
            });

            function showMessage(text, type) {
                message.textContent = text;
                message.className = 'message ' + type;
                message.style.display = 'block';
            }
        });
    </script>
</body>

</html>