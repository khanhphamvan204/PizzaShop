<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ComboController;
use App\Http\Controllers\ComboItemController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CrustController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SizeController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\RevenueController;

// =====================================
// 🛒 CART ROUTES (Giỏ hàng)
// =====================================
Route::get('/cart/products', [CartController::class, 'getAllProducts']); // 📋 Lấy tất cả sản phẩm trong giỏ (không cần auth)

Route::prefix('cart')->middleware('auth:api')->group(function () {
    Route::get('/', [CartController::class, 'index']);                      // 🛒 Xem giỏ hàng hiện tại
    Route::post('/items', [CartController::class, 'addItem']);              // ➕ Thêm sản phẩm vào giỏ
    Route::put('/items/{itemId}', [CartController::class, 'updateItem']);   // ✏️ Cập nhật số lượng item
    Route::delete('/items/{itemId}', [CartController::class, 'removeItem']); // 🗑️ Xóa item khỏi giỏ
    Route::delete('/clear', [CartController::class, 'clear']);              // 🧹 Xóa toàn bộ giỏ hàng
});

// =====================================
// 🎨 BANNER ROUTES (Banner quảng cáo)
// =====================================
Route::prefix('banners')->group(function () {
    Route::get('/', [BannerController::class, 'index']);                    // 📋 Danh sách banner
    Route::get('/{banner}', [BannerController::class, 'show']);             // 🔍 Chi tiết banner
    Route::middleware('check_role:admin')->group(function () {
        Route::post('/', [BannerController::class, 'store']);                   // ➕ Tạo banner mới
        Route::put('/{banner}', [BannerController::class, 'update']);           // ✏️ Cập nhật banner
        Route::delete('/{banner}', [BannerController::class, 'destroy']);       // 🗑️ Xóa banner
    });
});

// =====================================
// 🔐 AUTH ROUTES (Xác thực)
// =====================================
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');  // 🔑 Đăng nhập
    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me']);                    // 👤 Thông tin user hiện tại
        Route::post('logout', [AuthController::class, 'logout']);           // 🚪 Đăng xuất
        Route::post('refresh', [AuthController::class, 'refresh']);         // 🔄 Làm mới token
    });
});

// =====================================
// 📁 CATEGORY ROUTES (Danh mục)
// =====================================
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);                  // 📋 Danh sách danh mục
    Route::get('/{category}', [CategoryController::class, 'show']);         // 🔍 Chi tiết danh mục
    Route::middleware('check_role:admin')->group(function () {
        Route::post('/', [CategoryController::class, 'store']);                 // ➕ Tạo danh mục mới
        Route::put('/{category}', [CategoryController::class, 'update']);       // ✏️ Cập nhật danh mục
        Route::delete('/{category}', [CategoryController::class, 'destroy']);   // 🗑️ Xóa danh mục
    });
});

// =====================================
// 🍕 COMBO ROUTES (Combo sản phẩm)
// =====================================
Route::prefix('combos')->group(function () {
    Route::get('/active', [ComboController::class, 'active']);              // ✅ Combo đang hoạt động
    Route::get('/', [ComboController::class, 'index']);                     // 📋 Danh sách combo
    Route::get('/{id}', [ComboController::class, 'show']);                  // 🔍 Chi tiết combo
    Route::middleware('check_role:admin')->group(function () {
        Route::post('/', [ComboController::class, 'store']);                    // ➕ Tạo combo mới
        Route::put('/{id}', [ComboController::class, 'update']);                // ✏️ Cập nhật combo
        Route::delete('/{id}', [ComboController::class, 'destroy']);            // 🗑️ Xóa combo
    });
});

// =====================================
// 🍕📦 COMBO ITEMS ROUTES (Items trong combo)
// =====================================
Route::prefix('combo-items')->group(function () {
    Route::get('/', [ComboItemController::class, 'index']);                    // 📋 Danh sách combo items
    Route::get('/{id}', [ComboItemController::class, 'show']);              // 🔍 Chi tiết combo item
    Route::middleware('check_role:admin')->group(function () {
        Route::post('/', [ComboItemController::class, 'store']);                    // ➕ Tạo combo item mới
        Route::put('/{id}', [ComboItemController::class, 'update']);            // ✏️ Cập nhật combo item
        Route::delete('/{id}', [ComboItemController::class, 'destroy']);        // 🗑️ Xóa combo item
    });
});

// =====================================
// 📞 CONTACT ROUTES (Liên hệ)
// =====================================
Route::prefix('contacts')->group(function () {
    Route::get('/', [ContactController::class, 'index']);                   // 📋 Danh sách liên hệ (admin)
    Route::post('/', [ContactController::class, 'store']);                  // 📞 Gửi form liên hệ
    Route::get('/{id}', [ContactController::class, 'show']);                // 🔍 Chi tiết liên hệ (admin)
    Route::delete('/{id}', [ContactController::class, 'destroy']);          // 🗑️ Xóa liên hệ
});

// =====================================
// 🎫 COUPON ROUTES (Mã giảm giá)
// =====================================
Route::prefix('coupons')->group(function () {
    Route::get('/', [CouponController::class, 'index']);                    // 📋 Danh sách coupon
    Route::get('/{id}', [CouponController::class, 'show']);                 // 🔍 Chi tiết coupon
    Route::post('/validate', [CouponController::class, 'validate']);        // ✅ Kiểm tra mã coupon
    Route::middleware('check_role:admin')->group(function () {
        Route::post('/', [CouponController::class, 'store']);                   // ➕ Tạo coupon mới
        Route::put('/{id}', [CouponController::class, 'update']);               // ✏️ Cập nhật coupon
        Route::delete('/{id}', [CouponController::class, 'destroy']);           // 🗑️ Xóa coupon
    });
});

// =====================================
// 🥧 CRUST ROUTES (Đế bánh)
// =====================================
Route::prefix('crusts')->group(function () {
    Route::get('/', [CrustController::class, 'index']);                     // 📋 Danh sách đế bánh
    Route::get('/{id}', [CrustController::class, 'show']);                  // 🔍 Chi tiết đế bánh
    Route::middleware('check_role:admin')->group(function () {
        Route::post('/', [CrustController::class, 'store']);                    // ➕ Tạo đế bánh mới
        Route::put('/{id}', [CrustController::class, 'update']);                // ✏️ Cập nhật đế bánh
        Route::delete('/{id}', [CrustController::class, 'destroy']);            // 🗑️ Xóa đế bánh
    });
});

// =====================================
// ❓ FAQ ROUTES (Câu hỏi thường gặp)
// =====================================
Route::prefix('faqs')->group(function () {
    Route::get('/', [FaqController::class, 'index']);                       // 📋 Danh sách FAQ
    Route::get('/{id}', [FaqController::class, 'show']);                    // 🔍 Chi tiết FAQ
    Route::middleware('check_role:admin')->group(function () {
        Route::post('/', [FaqController::class, 'store']);                      // ➕ Tạo FAQ mới
        Route::put('/{id}', [FaqController::class, 'update']);                  // ✏️ Cập nhật FAQ
        Route::delete('/{id}', [FaqController::class, 'destroy']);              // 🗑️ Xóa FAQ
    });
});

// =====================================
// 📰 NEWS ROUTES (Tin tức)
// =====================================
Route::prefix('news')->group(function () {
    Route::get('/', [NewsController::class, 'index']);                      // 📋 Danh sách tin tức
    Route::get('/{id}', [NewsController::class, 'show']);                   // 🔍 Chi tiết tin tức
    Route::get('/latest/{count?}', [NewsController::class, 'latest']);      // 🔥 Tin tức mới nhất (limit)
    Route::middleware('check_role:admin')->group(function () {
        Route::post('/', [NewsController::class, 'store']);                     // ➕ Tạo tin tức mới
        Route::put('/{id}', [NewsController::class, 'update']);                 // ✏️ Cập nhật tin tức
        Route::delete('/{id}', [NewsController::class, 'destroy']);             // 🗑️ Xóa tin tức
    });
});

// =====================================
// 📦 ORDER ROUTES (Đơn hàng) - Cần đăng nhập
// =====================================
Route::prefix('orders')->middleware('auth:api')->group(function () {
    Route::get('/', [OrderController::class, 'index']);                     // 📋 Danh sách đơn hàng của user
    Route::post('/', [OrderController::class, 'store']);                    // 🛒 Tạo đơn hàng mới
    Route::get('/{id}', [OrderController::class, 'show']);                  // 🔍 Chi tiết đơn hàng
    Route::patch('/{id}/status', [OrderController::class, 'updateStatus']); // 📊 Cập nhật trạng thái đơn hàng
    Route::post('/{id}/cancel', [OrderController::class, 'cancel']);        // ❌ Hủy đơn hàng
});

// =====================================
// 📦📋 ORDER ITEMS ROUTES (Chi tiết đơn hàng) - Cần đăng nhập
// =====================================
Route::prefix('order-items')->middleware('auth:api')->group(function () {
    Route::get('/', [OrderItemController::class, 'index']);                 // 📋 Danh sách order items
    Route::post('/', [OrderItemController::class, 'store']);                // ➕ Thêm item vào đơn hàng
    Route::get('/{id}', [OrderItemController::class, 'show']);              // 🔍 Chi tiết order item
    Route::put('/{id}', [OrderItemController::class, 'update']);            // ✏️ Cập nhật order item
    Route::delete('/{id}', [OrderItemController::class, 'destroy']);        // 🗑️ Xóa order item

    Route::get('/by-order/{orderId}', [OrderItemController::class, 'getByOrder']); // 📦 Lấy items theo order ID

    // 📊 THỐNG KÊ
    Route::get('/stats/best-selling-products', [OrderItemController::class, 'bestSellingProducts']); // 🏆 Sản phẩm bán chạy
    Route::get('/stats/best-selling-combos', [OrderItemController::class, 'bestSellingCombos']);     // 🏆 Combo bán chạy
});

// =====================================
// 💳 PAYMENT ROUTES (Thanh toán) - Cần đăng nhập
// =====================================
Route::prefix('payments')->middleware('auth:api')->group(function () {
    Route::get('/', [PaymentController::class, 'index']);                   // 📋 Lịch sử thanh toán
    Route::post('/', [PaymentController::class, 'store']);                  // 💳 Tạo thanh toán mới
    Route::get('/{id}', [PaymentController::class, 'show']);                // 🔍 Chi tiết thanh toán
    Route::patch('/{id}/status', [PaymentController::class, 'updateStatus']); // 📊 Cập nhật trạng thái thanh toán
});

// =====================================
// 🍕 PRODUCT ROUTES (Sản phẩm)
// =====================================
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);                   // 📋 Danh sách sản phẩm
    Route::get('/featured', [ProductController::class, 'featured']);        // ⭐ Sản phẩm nổi bật
    Route::get('/{id}', [ProductController::class, 'show']);                // 🔍 Chi tiết sản phẩm
    Route::middleware('check_role:admin')->group(function () {
        Route::post('/', [ProductController::class, 'store']);                  // ➕ Tạo sản phẩm mới
        Route::put('/{id}', [ProductController::class, 'update']);              // ✏️ Cập nhật sản phẩm
        Route::delete('/{id}', [ProductController::class, 'destroy']);          // 🗑️ Xóa sản phẩm
    });
});

// =====================================
// 🍕🔄 PRODUCT VARIANTS ROUTES (Biến thể sản phẩm)
// =====================================
Route::prefix('product-variants')->group(function () {
    Route::get('/', [ProductVariantController::class, 'index']);            // 📋 Danh sách variants
    Route::get('/{id}', [ProductVariantController::class, 'show']);         // 🔍 Chi tiết variant
    Route::middleware('check_role:admin')->group(function () {
        Route::post('/', [ProductVariantController::class, 'store']);           // ➕ Tạo variant mới
        Route::put('/{id}', [ProductVariantController::class, 'update']);       // ✏️ Cập nhật variant
        Route::delete('/{id}', [ProductVariantController::class, 'destroy']);   // 🗑️ Xóa variant
    });
});

// =====================================
// ⭐ REVIEW ROUTES (Đánh giá) - Cần đăng nhập
// =====================================
Route::prefix('reviews')->group(function () {
    Route::get('/', [ReviewController::class, 'index']);                    // 📋 List all reviews
    Route::get('/{id}', [ReviewController::class, 'show']);                 // 🔍 View a specific review

    Route::middleware('auth:api')->group(function () {
        Route::post('/', [ReviewController::class, 'store']);               // ➕ Create a new review
        Route::put('/{id}', [ReviewController::class, 'update']);           // ✏️ Update a review (PUT)
        Route::patch('/{id}', [ReviewController::class, 'update']);         // ✏️ Update a review (PATCH)
        Route::delete('/{id}', [ReviewController::class, 'destroy']);       // 🗑️ Delete a review
    });
});

// =====================================
// 📏 SIZE ROUTES (Kích thước)
// =====================================
Route::prefix('sizes')->group(function () {
    Route::get('/', [SizeController::class, 'index']);                      // 📋 Danh sách kích thước
    Route::get('/{id}', [SizeController::class, 'show']);                   // 🔍 Chi tiết kích thước
    Route::middleware('check_role:admin')->group(function () {
        Route::post('/', [SizeController::class, 'store']);                     // ➕ Tạo kích thước mới
        Route::put('/{id}', [SizeController::class, 'update']);                 // ✏️ Cập nhật kích thước
        Route::delete('/{id}', [SizeController::class, 'destroy']);             // 🗑️ Xóa kích thước
    });
});

// =====================================
// 👥 USER ROUTES (Người dùng)
// =====================================
Route::prefix('users')->group(function () {
    Route::middleware('check_role:admin')->group(function () {
        Route::get('/', [UserController::class, 'index']);                      // 📋 Danh sách user (admin)
        Route::post('/', [UserController::class, 'store']);                     // ➕ Tạo user mới (đăng ký)
        Route::get('/{id}', [UserController::class, 'show']);                   // 🔍 Chi tiết user
        Route::delete('/{id}', [UserController::class, 'destroy']);             // 🗑️ Xóa user
        Route::patch('/{id}', [UserController::class, 'update']);                 // ✏️ Cập nhật user (PATCH)
    });
    Route::put('/{id}', [UserController::class, 'update'])->middleware('auth:api');                 // ✏️ Cập nhật user (PUT)
    Route::post('/change-password', [UserController::class, 'changePassword'])->middleware('auth:api'); // 🔒 Đổi mật khẩu (cần auth)
});

// =====================================
// 👤 PROFILE ROUTES (Hồ sơ cá nhân) - Cần đăng nhập
// =====================================
Route::middleware('auth:api')->group(function () {
    Route::get('/profile', [UserController::class, 'profile']);             // 👤 Xem hồ sơ cá nhân
});

// =====================================
// 🔒 PASSWORD RESET ROUTES (Đặt lại mật khẩu)
// =====================================
Route::post('/password/forgot', [UserController::class, 'sendPasswordResetEmail']);      // 📧 Gửi email đặt lại mật khẩu
Route::post('/password/verify-token', [UserController::class, 'verifyResetToken']);       // ✅ Xác thực token reset
Route::post('/password/reset', [UserController::class, 'resetPassword']);                 // 🔑 Đặt lại mật khẩu mới
Route::post('/password/cancel-reset', [UserController::class, 'cancelPasswordReset']);    // ❌ Hủy yêu cầu reset

// =====================================
// 🌐 WEB INTERFACE ROUTES (Giao diện web)
// =====================================
Route::get('/reset-password', function (Request $request) {                // 📝 Form đặt lại mật khẩu (Web)
    $email = $request->query('email');
    $token = $request->query('token');
    return view('reset-password-form', compact('email', 'token'));
});


Route::prefix('verification')->group(function () {
    Route::post('/send-email-otp', [EmailVerificationController::class, 'sendEmailOTP']);
    Route::post('/verify-email-otp', [EmailVerificationController::class, 'verifyEmailOTP']);
    Route::post('/resend-email-otp', [EmailVerificationController::class, 'resendEmailOTP']);
    Route::post('/cancel-email-verification', [EmailVerificationController::class, 'cancelEmailVerification']);
    Route::post('/check-verification-status', [EmailVerificationController::class, 'checkVerificationStatus']);
});

Route::prefix('revenue')->middleware('check_role:admin')->group(function () {
    Route::get('/daily', [RevenueController::class, 'dailyRevenue']);
    Route::get('/weekly', [RevenueController::class, 'weeklyRevenue']);
    Route::get('/monthly', [RevenueController::class, 'monthlyRevenue']);
    Route::get('/yearly', [RevenueController::class, 'yearlyRevenue']);

    // Route::get('/category', [RevenueController::class, 'revenueByCategory']);
    Route::get('/top-products', [RevenueController::class, 'topSellingProducts']);
    Route::get('/combo', [RevenueController::class, 'comboRevenue']);

    Route::get('/top-customers', [RevenueController::class, 'topCustomers']);

    Route::get('/coupons', [RevenueController::class, 'revenueWithCoupons']);

    Route::get('/dashboard', [RevenueController::class, 'dashboardStats']);
});
