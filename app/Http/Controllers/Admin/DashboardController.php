<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'students' => \App\Models\User::role('student')->count(),
            'admins' => \App\Models\User::role('admin')->count(),
            'exercises' => \App\Models\Exercise::count(),
            'exercise_types' => \App\Models\ExerciseType::count(),
            'units' => \App\Models\Unit::count(),
            'lessons' => \App\Models\Lesson::count(),
            'resources' => \App\Models\Resource::count(),
            'attempts' => \App\Models\UserExerciseAttempt::count(),
        ];
        return Inertia::render("Dashboard", compact('stats'));
    }
}
