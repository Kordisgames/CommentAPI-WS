<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use Illuminate\Support\Facades\Route;

$api_version = 'v1';
function authRoutes(): void
{
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
}

function routeApi(): void
{
    // Маршруты аутентификации
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);

    // Маршрут для получения списка новостей
    Route::get('/news', [\App\Http\Controllers\Api\NewsController::class, 'index']);

    // Маршруты для комментариев
    Route::get('/news/{news}/comments', [CommentController::class, 'index']);
    Route::post('/news/{news}/comments', [CommentController::class, 'store']);
    Route::get('/comments/search', [CommentController::class, 'search']);
    Route::get('/comments/{comment}', [CommentController::class, 'show']);
    Route::put('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
}

// Регистрируем маршруты API с префиксом v1
Route::prefix($api_version)->group(function () {
    // Публичные маршруты аутентификации
    authRoutes();

    // Защищенные маршруты
    Route::middleware('auth:sanctum')->group(function () {
        routeApi();
    });
});
