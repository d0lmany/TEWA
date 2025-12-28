<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ClaimController;
use App\Http\Controllers\API\PickupController;
use App\Http\Controllers\API\AddressController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CartItemController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\FavoriteController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ShopController;

Route::get('/', fn () => response()->json(['message' => 'Hello, World!']));
Route::prefix('v1')->middleware('throttle:75,1')->group(function () {
    Route::get('/', fn () => response()->json(['message' => 'It\'s API v1']));
    // публичные маршруты
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth', [AuthController::class, 'register']);
    
    Route::apiResource('categories', CategoryController::class)->only(['index']);
    Route::apiResource('products', ProductController::class)->only(['index', 'show']);
    Route::get('shops/{shop}', [ShopController::class, 'show']);
    
    // маршруты для авторизованных
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth', [AuthController::class, 'show']);
        Route::post('auth/update', [AuthController::class, 'update']);
        Route::patch('auth/update/password', [AuthController::class, 'changePassword']);
        Route::delete('auth', [AuthController::class, 'destroy']);
        // корзина
        Route::apiResource('cart', CartItemController::class)->except('show');
        Route::get('cart/low', [CartItemController::class, 'indexBg']);
        Route::post('cart/destroy', [CartItemController::class, 'destroyRange']);
        // избранное
        Route::patch('favorite/item/{item}', [FavoriteController::class, 'changeList']);
        Route::apiResource('favorite', FavoriteController::class)->except('show');
        Route::get('favorite/low', [FavoriteController::class, 'indexBg']);
        Route::post('favorite/toggle', [FavoriteController::class, 'toggle']);
        Route::delete('favorite/clear/{favorite}', [FavoriteController::class, 'clear']);
        // жалобы
        Route::post('claims', [ClaimController::class, 'store']);
        // товары
        Route::middleware('shop.owner')->group(function () {
            Route::middleware('product.owner')->group(function () {
                Route::apiResource('products', ProductController::class)->only(['update', 'destroy']);
            });
            Route::post('products', [ProductController::class, 'store']);
        });
        // адреса и ПВЗ
        Route::apiResource('addresses', AddressController::class)->except('show');
        Route::get('pickups', [PickupController::class, 'index']);
        // заказы
        Route::get('orders', [OrderController::class, 'index']);
        Route::post('orders', [OrderController::class, 'store']);
        // маршруты для админа
        Route::middleware('adminOnly')->group(function () {
            Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
        });
    });
});
Route::fallback(function ($path) {
    if (preg_match('/\.(env|git|sql|bak)|wp-|phpmyadmin|adminer/', $path)) {
        Log::channel('security')->warning('Honeypot triggered', [
            'ip' => request()->ip(),
            'path' => $path,
            'user_agent' => request()->userAgent(),
        ]);
        
        return response()->json([
            'error' => 'Not found',
            'message' => 'Try this instead: https://youtu.be/dQw4w9WgXcQ',
        ], 404);
    }
    
    abort(404, "Route '{$path}' not found");
});