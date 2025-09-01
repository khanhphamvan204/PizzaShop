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


Route::get('/cart/products', [CartController::class, 'getAllProducts']);
Route::prefix('cart')->middleware('auth:api')->group(function () {
    Route::get('/', [CartController::class, 'index']);
    Route::post('/items', [CartController::class, 'addItem']);
    Route::put('/items/{itemId}', [CartController::class, 'updateItem']);
    Route::delete('/items/{itemId}', [CartController::class, 'removeItem']);
    Route::delete('/clear', [CartController::class, 'clear']);
});



Route::prefix('banners')->group(function () {
    Route::get('/', [BannerController::class, 'index']);
    Route::post('/', [BannerController::class, 'store']);
    Route::get('/{banner}', [BannerController::class, 'show']);
    Route::put('/{banner}', [BannerController::class, 'update']);
    Route::delete('/{banner}', [BannerController::class, 'destroy']);
});


Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });
});


Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::post('/', [CategoryController::class, 'store']);
    Route::get('/{category}', [CategoryController::class, 'show']);
    Route::put('/{category}', [CategoryController::class, 'update']);
    Route::delete('/{category}', [CategoryController::class, 'destroy']);
});

Route::prefix('combos')->group(function () {
    Route::get('/active', [ComboController::class, 'active']);
    Route::get('/', [ComboController::class, 'index']);
    Route::post('/', [ComboController::class, 'store']);
    Route::get('/{id}', [ComboController::class, 'show']);
    Route::put('/{id}', [ComboController::class, 'update']);
    Route::delete('/{id}', [ComboController::class, 'destroy']);
});
Route::prefix('combo-items')->group(function () {
    Route::get('/{id}', [ComboItemController::class, 'show']);
    Route::put('/{id}', [ComboItemController::class, 'update']);
    Route::delete('/{id}', [ComboItemController::class, 'destroy']);
});
Route::prefix('contacts')->group(function () {
    Route::get('/', [ContactController::class, 'index']);
    Route::post('/', [ContactController::class, 'store']);
    Route::get('/{id}', [ContactController::class, 'show']);
    Route::delete('/{id}', [ContactController::class, 'destroy']);
});

Route::prefix('coupons')->group(function () {
    Route::get('/', [CouponController::class, 'index']);
    Route::post('/', [CouponController::class, 'store']);
    Route::get('/{id}', [CouponController::class, 'show']);
    Route::put('/{id}', [CouponController::class, 'update']);
    Route::delete('/{id}', [CouponController::class, 'destroy']);
    Route::post('/validate', [CouponController::class, 'validate']);
});
Route::prefix('crusts')->group(function () {
    Route::get('/', [CrustController::class, 'index']);
    Route::post('/', [CrustController::class, 'store']);
    Route::get('/{id}', [CrustController::class, 'show']);
    Route::put('/{id}', [CrustController::class, 'update']);
    Route::delete('/{id}', [CrustController::class, 'destroy']);
});
Route::prefix('faqs')->group(function () {
    Route::get('/', [FaqController::class, 'index']);
    Route::post('/', [FaqController::class, 'store']);
    Route::get('/{id}', [FaqController::class, 'show']);
    Route::put('/{id}', [FaqController::class, 'update']);
    Route::delete('/{id}', [FaqController::class, 'destroy']);
});
Route::prefix('news')->group(function () {
    Route::get('/', [NewsController::class, 'index']);
    Route::post('/', [NewsController::class, 'store']);
    Route::get('/latest/{count?}', [NewsController::class, 'latest']);
    Route::get('/{id}', [NewsController::class, 'show']);
    Route::put('/{id}', [NewsController::class, 'update']);
    Route::delete('/{id}', [NewsController::class, 'destroy']);
});

Route::prefix('orders')->middleware('auth:api')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::post('/', [OrderController::class, 'store']);
    Route::get('/{id}', [OrderController::class, 'show']);
    Route::patch('/{id}/status', [OrderController::class, 'updateStatus']);
    Route::post('/{id}/cancel', [OrderController::class, 'cancel']);
});

Route::prefix('order-items')->middleware('auth:api')->group(function () {
    Route::get('/', [OrderItemController::class, 'index']);
    Route::post('/', [OrderItemController::class, 'store']);
    Route::get('/{id}', [OrderItemController::class, 'show']);
    Route::put('/{id}', [OrderItemController::class, 'update']);
    Route::delete('/{id}', [OrderItemController::class, 'destroy']);

    Route::get('/by-order/{orderId}', [OrderItemController::class, 'getByOrder']);

    Route::get('/stats/best-selling-products', [OrderItemController::class, 'bestSellingProducts']);
    Route::get('/stats/best-selling-combos', [OrderItemController::class, 'bestSellingCombos']);
});

Route::prefix('payments')->middleware('auth:api')->group(function () {
    Route::get('/', [PaymentController::class, 'index']);
    Route::post('/', [PaymentController::class, 'store']);
    Route::get('/{id}', [PaymentController::class, 'show']);
    Route::patch('/{id}/status', [PaymentController::class, 'updateStatus']);
});
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::post('/', [ProductController::class, 'store']);
    Route::get('/featured', [ProductController::class, 'featured']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::put('/{id}', [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);
});

Route::prefix('product-variants')->group(function () {
    Route::get('/', [ProductVariantController::class, 'index']);
    Route::post('/', [ProductVariantController::class, 'store']);
    Route::get('/{id}', [ProductVariantController::class, 'show']);
    Route::put('/{id}', [ProductVariantController::class, 'update']);
    Route::delete('/{id}', [ProductVariantController::class, 'destroy']);
});

Route::prefix('reviews')->middleware('auth:api')->group(function () {
    Route::get('/', [ReviewController::class, 'index']);
    Route::post('/', [ReviewController::class, 'store']);
    Route::get('/{id}', [ReviewController::class, 'show']);
    Route::put('/{id}', [ReviewController::class, 'update']);
    Route::patch('/{id}', [ReviewController::class, 'update']);
    Route::delete('/{id}', [ReviewController::class, 'destroy']);
});

Route::prefix('sizes')->group(function () {
    Route::get('/', [SizeController::class, 'index']);
    Route::post('/', [SizeController::class, 'store']);
    Route::get('/{id}', [SizeController::class, 'show']);
    Route::put('/{id}', [SizeController::class, 'update']);
    Route::delete('/{id}', [SizeController::class, 'destroy']);
});


Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::patch('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
});

Route::middleware('auth:api')->group(function () {
    Route::get('/profile', [UserController::class, 'profile']);
});


Route::post('/password/forgot', [UserController::class, 'sendPasswordResetEmail']);
Route::post('/password/verify-token', [UserController::class, 'verifyResetToken']);
Route::post('/password/reset', [UserController::class, 'resetPassword']);
Route::post('/password/cancel-reset', [UserController::class, 'cancelPasswordReset']);

Route::get('/reset-password', function (Request $request) {
    $email = $request->query('email');
    $token = $request->query('token');
    return view('reset-password-form', compact('email', 'token'));
});
