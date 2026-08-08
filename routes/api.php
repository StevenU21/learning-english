<?php

use App\Http\Controllers\API\Auth\GoogleController;
use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Auth\RegisterController;
use App\Http\Controllers\API\Profile\ProfileController;
use App\Http\Controllers\API\Student\ExerciseController;
use App\Http\Controllers\API\Student\LessonController;
use App\Http\Controllers\API\Student\ResourceController;
use App\Http\Controllers\API\Student\TextChatController;
use App\Http\Controllers\API\Student\UnitController;
use App\Http\Controllers\API\Student\VoiceChatController;
use Illuminate\Support\Facades\Route;

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);
Route::post('google', [GoogleController::class, 'authenticate']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [LoginController::class, 'logout']);

    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('user', [ProfileController::class, 'updateUser']);
        Route::put('info', [ProfileController::class, 'updateProfile']);
        Route::delete('/', [ProfileController::class, 'destroy']);
    });

    Route::get('units', [UnitController::class, 'index']);

    Route::get('units/{unit}/lessons', [LessonController::class, 'index']);
    Route::get('units/{unit}/lessons/{lesson}', [LessonController::class, 'show']);

    Route::get('units/{unit}/resources', [ResourceController::class, 'index']);
    Route::get('resources/{resource}/download', [ResourceController::class, 'download']);

    Route::get('lessons/{lesson}/exercises', [ExerciseController::class, 'index']);
    Route::post('exercises/attempts', [ExerciseController::class, 'store']);

    Route::get('chat/text', [TextChatController::class, 'index']);
    Route::post('chat/text', [TextChatController::class, 'sendMessage']);

    Route::get('chat/voice', [VoiceChatController::class, 'index']);
    Route::post('chat/voice', [VoiceChatController::class, 'createSession']);
});
