<?php

use App\Http\Controllers\Admin\ExerciseController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\LevelController;
use App\Http\Controllers\Admin\ProgressController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\UnitController;
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

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::prefix('/profile')->name('profile.')->controller(ProfileController::class)->group(function () {
        // Profile views
        Route::get('/view', 'index')->name('index');
        Route::get('/', 'edit')->name('edit');
        Route::patch('/user', 'updateUser')->name('user.update');
        Route::post('/profile', 'updateProfile')->name('profile.update');
        Route::delete('/', 'destroy')->name('destroy');
    });

    // Rutas para estudiantes - Unidades
    Route::prefix('student')->name('student.')->group(function () {
        Route::get('units', [\App\Http\Controllers\Student\UnitController::class, 'index'])->name('units.index');
        // Lessons listing for a Unit
        Route::get('units/{unit}/lessons', [\App\Http\Controllers\Student\LessonController::class, 'index'])->name('units.lessons.index');
        // Resources for a Unit
        Route::get('units/{unit}/resources', [\App\Http\Controllers\Student\ResourceController::class, 'index'])->name('units.resources.index');
        Route::get('resources/{resource}/download', [\App\Http\Controllers\Student\ResourceController::class, 'download'])->name('resources.download');
        // Exercise sequence for a Lesson within a Unit
        Route::get('units/{unit}/lessons/{lesson}/exercise', [\App\Http\Controllers\Student\ExerciseController::class, 'showSequence'])->name('units.lessons.sequence');
        // Lesson summary within a Unit
        Route::get('units/{unit}/lessons/{lesson}', [\App\Http\Controllers\Student\LessonController::class, 'show'])->name('units.lessons.show');
        Route::post('exercises/attempts-batch', [\App\Http\Controllers\Student\ExerciseController::class, 'storeAttemptsBatch'])->name('exercises.attemptsBatch');
    });

    Route::middleware('role:admin')->group(function () {
        Route::resource('levels', LevelController::class);
        Route::resource('units', UnitController::class);
        Route::resource('lessons', LessonController::class);
        Route::resource('resources', ResourceController::class);
        Route::get('resources/{resource}/download', [ResourceController::class, 'download'])->name('resources.download');
        Route::resource('exercises', ExerciseController::class);
        Route::get('progress', [ProgressController::class, 'index'])->name('admin.progress.index');
    });

    Route::get('/admin/progress/{user}', [ProgressController::class, 'show'])->name('admin.progress.show');

});

require __DIR__ . '/auth.php';

// Social auth routes (guest only)
Route::middleware('guest')->group(function () {
    // Google
    Route::get('/auth/google/redirect', [\App\Http\Controllers\Auth\GoogleController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleController::class, 'callback'])->name('auth.google.callback');

    // GitHub
    Route::get('/auth/github/redirect', [\App\Http\Controllers\Auth\GithubController::class, 'redirect'])->name('auth.github.redirect');
    Route::get('/auth/github/callback', [\App\Http\Controllers\Auth\GithubController::class, 'callback'])->name('auth.github.callback');
});
