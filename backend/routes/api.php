<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CartItemController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\FavoriteListItemController;
use Illuminate\Support\Facades\Route;

// публичный маршрут
Route::middleware('throttle:api')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/register', [AuthController::class, 'register']);
    
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{category}', [CategoryController::class, 'show']);
    
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{product}', [ProductController::class, 'show']);
});
// маршрут для авторизованных
Route::middleware(['throttle:api', 'auth:sanctum'])->group(function () {
    Route::get('auth/logout', [AuthController::class, 'logout']);

    Route::get('cart', [CartItemController::class, 'index']);
    Route::post('cart', [CartItemController::class, 'add']);
    Route::post('cart/reduce', [CartItemController::class, 'decrement']);
    Route::post('cart/remove', [CartItemController::class, 'remove']);
    
    Route::post('favorite', [FavoriteListItemController::class, 'toggle']);
    // TODO: надо перенести к продавцам
    //Route::post('products', [ProductController::class, 'store']);
    //Route::put('products/{product}', [ProductController::class, 'update']);
    //Route::patch('products/{product}', [ProductController::class, 'update']);
    //Route::delete('products/{product}', [ProductController::class, 'destroy']);

    // маршрут для админа
    Route::middleware('admin')->group(function () {
        Route::post('categories', [CategoryController::class, 'store']);
        Route::put('categories/{category}', [CategoryController::class, 'update']);
        Route::patch('categories/{category}', [CategoryController::class, 'update']);
        Route::delete('categories/{category}', [CategoryController::class, 'destroy']);
    });
});