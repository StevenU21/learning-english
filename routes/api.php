<?php

use App\Http\Controllers\API\Auth\GoogleController;
use App\Http\Controllers\API\Profile\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\Auth\RegisterController;
use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\UnitController;
use App\Http\Controllers\API\LessonController;

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
    // Units API routes
    Route::get('units', [UnitController::class, 'index']);
    Route::get('units/{unit}', [UnitController::class, 'show']);
    // Lessons API routes
    Route::get('lessons', [LessonController::class, 'index']);
    Route::get('lessons/{lesson}', [LessonController::class, 'show']);
    // Resources API routes
    Route::get('units/{unit}/resources', [App\Http\Controllers\API\ResourceController::class, 'index']);
    Route::get('resources/{resource}/download', [App\Http\Controllers\API\ResourceController::class, 'download']);
});
