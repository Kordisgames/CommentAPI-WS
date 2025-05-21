<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\NewsController;
use Illuminate\Support\Facades\Route;

// Регистрируем маршруты API с префиксом v1
Route::prefix('v1')->group(function () {
    // Публичные маршруты аутентификации
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Защищенные маршруты
    Route::middleware('auth:sanctum')->group(function () {
        // Маршруты аутентификации
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/user', [AuthController::class, 'user']);

        // Маршрут для получения списка новостей
        Route::get('/news', [NewsController::class, 'index']);

        // Маршруты для комментариев
        Route::get('/news/{news}/comments', [CommentController::class, 'index']);
        Route::post('/news/{news}/comments', [CommentController::class, 'store']);
        Route::get('/comments/search', [CommentController::class, 'search']);
        Route::get('/comments/{comment}', [CommentController::class, 'show']);
        Route::put('/comments/{comment}', [CommentController::class, 'update']);
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    });
});
