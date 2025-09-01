<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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
