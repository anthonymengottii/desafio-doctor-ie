<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

// Autenticacao. Login/registro com throttle para mitigar brute-force.
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

// Livros. Todas as rotas protegidas por token.
Route::middleware('auth:sanctum')->group(function () {
    // Rotas de similaridade antes do apiResource para nao colidir com {book}.
    Route::get('books/similares', [BookController::class, 'similarByText']);
    Route::get('books/{book}/similares', [BookController::class, 'similar']);

    Route::apiResource('books', BookController::class);
});
