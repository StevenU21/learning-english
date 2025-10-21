<?php

use App\Http\Controllers\API\Auth\GoogleController;
use App\Http\Controllers\API\Profile\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\Auth\RegisterController;
use App\Http\Controllers\API\Auth\LoginController;

// Rutas públicas de autenticación
Route::post('auth/register', [RegisterController::class, 'register']);
Route::post('auth/login', [LoginController::class, 'login']);
Route::post('auth/google', [GoogleController::class, 'authenticate']);

// Rutas protegidas por Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [LoginController::class, 'logout']);
    // Profile routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('user', [ProfileController::class, 'updateUser']);
        Route::put('info', [ProfileController::class, 'updateProfile']);
        Route::delete('/', [ProfileController::class, 'destroy']);
    });
});
