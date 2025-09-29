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
        Route::get('/', 'edit')->name('edit');
        Route::patch('/user', 'updateUser')->name('user.update');
        Route::post('/profile', 'updateProfile')->name('profile.update');
        Route::delete('/', 'destroy')->name('destroy');
    });

    // Rutas para estudiantes - Unidades
    Route::prefix('student')->name('student.')->group(function () {
        Route::get('units', [\App\Http\Controllers\Student\UnitController::class, 'index'])->name('units.index');
        Route::get('units/{id}/start', [\App\Http\Controllers\Student\LessonController::class, 'index'])->name('units.start');
    Route::get('lessons/{id}/start', [\App\Http\Controllers\Student\ExerciseController::class, 'showSequence'])->name('lessons.start');
    // Route for lesson summary
    Route::get('lessons/{id}', [\App\Http\Controllers\Student\LessonController::class, 'show'])->name('lessons.show');
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
