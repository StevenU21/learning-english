<?php

use App\Http\Controllers\Admin\ExerciseController;
use App\Http\Controllers\Admin\ExerciseTypeController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\LevelController;
use App\Http\Controllers\Admin\ProgressController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Student\TextChatController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Auth\GithubController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware('guest')->group(function () {
    // Google
    Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');

    // GitHub
    Route::get('/auth/github/redirect', [GithubController::class, 'redirect'])->name('auth.github.redirect');
    Route::get('/auth/github/callback', [GithubController::class, 'callback'])->name('auth.github.callback');
});


Route::middleware('auth')->group(function () {

    Route::prefix('/profile')->name('profile.')->controller(ProfileController::class)->group(function () {
        // Profile views
        Route::get('/view', 'index')->name('index');
        Route::get('/', 'edit')->name('edit');
        Route::patch('/user', 'updateUser')->name('user.update');
        Route::post('/profile', 'updateProfile')->name('profile.update');
        Route::delete('/', 'destroy')->name('destroy');
    });

    Route::prefix('student')->name('student.')->group(function () {
        Route::get('units', [\App\Http\Controllers\Student\UnitController::class, 'index'])->name('units.index');
        Route::get('units/{unit}/lessons', [\App\Http\Controllers\Student\LessonController::class, 'index'])->name('units.lessons.index');
        Route::get('units/{unit}/resources', [\App\Http\Controllers\Student\ResourceController::class, 'index'])->name('units.resources.index');
        Route::get('resources/{resource}/download', [\App\Http\Controllers\Student\ResourceController::class, 'download'])->name('resources.download');
        Route::get('units/{unit}/lessons/{lesson}/exercise', [\App\Http\Controllers\Student\ExerciseController::class, 'showSequence'])->name('units.lessons.sequence');
        Route::get('units/{unit}/lessons/{lesson}', [\App\Http\Controllers\Student\LessonController::class, 'show'])->name('units.lessons.show');
        Route::post('exercises/attempts-batch', [\App\Http\Controllers\Student\ExerciseController::class, 'storeAttemptsBatch'])->name('exercises.attemptsBatch');
        Route::post('say-the-phrase/attempt', [\App\Http\Controllers\Student\SayThePhraseController::class, 'attempt'])->name('say-the-phrase.attempt');
        Route::get('voice-chat', [\App\Http\Controllers\Student\VoiceChatController::class, 'index'])->name('voice-chat.index');
        Route::post('voice-chat/session', [\App\Http\Controllers\Student\VoiceChatController::class, 'createSession'])->name('voice-chat.session');
        Route::get('text-chat', [TextChatController::class, 'index'])->name('text-chat.index');
        Route::post('text-chat/message', [TextChatController::class, 'sendMessage'])->name('text-chat.message');
    });

    Route::middleware('role:admin')->group(function () {
        Route::resource('levels', LevelController::class);
        Route::resource('units', UnitController::class);
        Route::resource('lessons', LessonController::class);
        Route::resource('resources', ResourceController::class);
        Route::resource('exercise-types', ExerciseTypeController::class);

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('resources/{resource}/download', [ResourceController::class, 'download'])->name('resources.download');
        Route::resource('exercises', ExerciseController::class);
        Route::get('progress', [ProgressController::class, 'index'])->name('admin.progress.index');
        Route::get('/admin/progress/{user}', [ProgressController::class, 'show'])->name('admin.progress.show');
        Route::get('/admin/progress/{user}/reporte', [ProgressController::class, 'report'])->name('admin.progress.report');
        Route::get('/admin/progress/{user}/exercise/{exercise}/attempts', [ProgressController::class, 'exerciseAttempts']);
    });
});

require __DIR__ . '/auth.php';
