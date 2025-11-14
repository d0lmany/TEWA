<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CartItemController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ClaimController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\FavoriteListItemController;
use App\Http\Controllers\API\FavoriteListController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
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
        // корзина
        Route::apiResource('cart', CartItemController::class);
        Route::get('lowCart', [CartItemController::class, 'indexBg']);
        // избранное
        Route::get('lowFavorite', [FavoriteListController::class, 'indexBg']);
        Route::post('favorite', [FavoriteListItemController::class, 'toggle']);
        // жалобы
        Route::post('claims', [ClaimController::class, 'store']);
        // товары
        Route::middleware('shop.owner')->group(function () {
            Route::middleware('product.owner')->group(function () {
                Route::put('products/{product}', [ProductController::class, 'update']);
                Route::patch('products/{product}', [ProductController::class, 'update']);
                Route::delete('products/{product}', [ProductController::class, 'destroy']);
            });
            Route::post('products', [ProductController::class, 'store']);
        });
        // маршрут для админа
        Route::middleware('admin')->group(function () {
            Route::post('categories', [CategoryController::class, 'store']);
            Route::put('categories/{category}', [CategoryController::class, 'update']);
            Route::patch('categories/{category}', [CategoryController::class, 'update']);
            Route::delete('categories/{category}', [CategoryController::class, 'destroy']);
        });
    });
});