<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\CartController;

Route::get('/contacts', [ContactController::class, 'index']);
Route::post('/contacts', [ContactController::class, 'store']);
Route::get('/contacts/{contact}', [ContactController::class, 'show']);
Route::put('/contacts/{contact}', [ContactController::class, 'update']);
Route::delete('/contacts/{contact}', [ContactController::class, 'destroy']);


Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/featured', [ProductController::class, 'featured']);

Route::get('/banners', [BannerController::class, 'index']);

Route::middleware(['api', 'session'])->prefix('cart')->group(function () {
    // Tìm kiếm sản phẩm bằng natural language
    Route::post('/search', [CartController::class, 'searchProducts']);

    // Lấy tất cả sản phẩm
    Route::get('/products', [CartController::class, 'getAllProducts']);

    // Quản lý giỏ hàng
    Route::get('/', [CartController::class, 'index']);
    Route::post('/add', [CartController::class, 'addItem']);
    Route::put('/update/{itemId}', [CartController::class, 'updateItem']);
    Route::delete('/remove/{itemId}', [CartController::class, 'removeItem']);
    Route::delete('/clear', [CartController::class, 'clear']);
});
