<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Exercise;
use App\Models\Unit;
use App\Models\Lesson;
use App\Models\UserExerciseAttempt;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'students' => User::role('student')->count(),
            'admins' => User::role('admin')->count(),
            'exercises' => Exercise::count(),
            'units' => Unit::count(),
            'lessons' => Lesson::count(),
        ];

        // Cantidad total de intentos
        $stats['total_attempts'] = UserExerciseAttempt::count();

        // Porcentaje de estudiantes que completaron todas las unidades
        $totalStudents = $stats['students'];
        $totalUnits = $stats['units'];
        $studentsWithAllUnits = DB::table('unit_user_progress')
            ->where('status', 'completado')
            ->select('user_id', DB::raw('count(*) as completed_units'))
            ->groupBy('user_id')
            ->having('completed_units', '>=', $totalUnits)
            ->count();
        $stats['students_completed_all_units'] = $totalStudents > 0 ? round($studentsWithAllUnits * 100 / $totalStudents, 1) : 0;

        $totalMinutes = DB::table('lesson_user_progress')
            ->where('status', 'completado')
            ->join('lessons', 'lesson_user_progress.lesson_id', '=', 'lessons.id')
            ->sum('lessons.duration');
        $stats['learning_hours'] = round($totalMinutes / 60, 1);

        $totalAttempts = UserExerciseAttempt::count();
        $totalCorrect = UserExerciseAttempt::where('is_correct', true)->count();
        $stats['global_accuracy'] = $totalAttempts > 0 ? round($totalCorrect * 100 / $totalAttempts, 1) : 0;

        return Inertia::render("Dashboard", compact('stats'));
    }
}
