<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/reset-password', function (Request $request) {
    $email = $request->query('email');
    $token = $request->query('token');

    if (!$email || !$token) {
        abort(400, 'Missing email or token');
    }

    return view('reset-password-form', compact('email', 'token'));
});
Route::get('/verify-email', [UserController::class, 'showVerificationResult'])->name('verify.email');
Route::get('/resend-verification', [UserController::class, 'resendVerificationOTP'])->name('resend.verification');
