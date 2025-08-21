<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProductController;

Route::get('/contacts', [ContactController::class, 'index']);
Route::post('/contacts', [ContactController::class, 'store']);
Route::get('/contacts/{contact}', [ContactController::class, 'show']);
Route::put('/contacts/{contact}', [ContactController::class, 'update']); 
Route::delete('/contacts/{contact}', [ContactController::class, 'destroy']); 


Route::get('/products', [ProductController::class,'index']);
Route::get('/products/featured', [ProductController::class,'featured']);